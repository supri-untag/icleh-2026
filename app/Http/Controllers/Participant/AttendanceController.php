<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ProgramSchedule;
use App\Models\Registration;
use App\Services\ConferenceContext;
use App\Services\Mail\WorkflowMailService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JsonException;

class AttendanceController extends Controller
{
    public function __construct(private ConferenceContext $conferenceContext) {}

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:4096']]);
        $conference = $this->conferenceContext->current();

        $code = trim($data['code']);
        $normalizedCode = strtoupper(str_replace([' ', '-'], '', $code));
        $isManualCode = preg_match('/^[A-F0-9]{8}$/', $normalizedCode) === 1;

        if ($isManualCode) {
            $code = Cache::get('attendance-code:'.$conference->id.':'.$normalizedCode);
            if (! is_string($code)) {
                throw ValidationException::withMessages(['code' => 'Attendance code is invalid or expired. Ask the committee for a new code.']);
            }
        }

        try {
            $payload = json_decode(Crypt::decryptString($code), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|JsonException) {
            throw ValidationException::withMessages(['code' => 'Invalid attendance code. Scan the committee QR or enter the 8-character attendance code.']);
        }

        if (! is_array($payload) || ($payload['purpose'] ?? null) !== 'attendance'
            || ($payload['conference_id'] ?? null) !== $conference->id
            || ($payload['expires_at'] ?? 0) <= now()->timestamp
            || ($payload['attendance_date'] ?? null) !== now($conference->timezone)->toDateString()) {
            throw ValidationException::withMessages(['code' => 'This attendance QR is expired or belongs to another conference. Ask the committee for a new QR.']);
        }

        $scheduleId = $payload['program_schedule_id'] ?? null;
        if ($scheduleId !== null && ! ProgramSchedule::query()->published()->whereKey($scheduleId)
            ->whereHas('day', fn ($query) => $query->whereBelongsTo($conference))->exists()) {
            throw ValidationException::withMessages(['code' => 'This attendance session is no longer available.']);
        }

        $attendance = DB::transaction(function () use ($request, $conference, $scheduleId, $payload, $isManualCode): Attendance {
            $registration = Registration::query()->whereBelongsTo($conference)
                ->whereBelongsTo($request->user())->lockForUpdate()->first();

            if (! $registration) {
                throw ValidationException::withMessages(['code' => 'Complete conference registration before checking in.']);
            }

            $existingAttendance = $registration->attendances()
                ->where('program_schedule_id', $scheduleId)
                ->whereDate('attendance_date', $payload['attendance_date'])->first();

            if ($existingAttendance) {
                return $existingAttendance;
            }

            $attendance = $registration->attendances()->create([
                'program_schedule_id' => $scheduleId,
                'attendance_date' => $payload['attendance_date'],
                'recorded_by' => $request->user()->id,
                'checked_in_at' => now(),
                'method' => $isManualCode ? 'manual' : 'qr',
            ]);
            app(WorkflowMailService::class)->user($request->user(), 'attendance_recorded', 'Attendance recorded', [
                'attendance_date' => $payload['attendance_date'],
                'session_title' => $scheduleId ? ProgramSchedule::query()->findOrFail($scheduleId)->title : 'General attendance',
            ], $conference);

            return $attendance;
        });

        $message = $attendance->wasRecentlyCreated ? 'Attendance recorded successfully.' : 'You have already checked in for this session.';

        if (! $request->expectsJson()) {
            return redirect()->route('participant.attendance')->with('status', $message);
        }

        return response()->json([
            'message' => $message,
            'redirect_url' => route('participant.attendance'),
        ]);
    }
}
