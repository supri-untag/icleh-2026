<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramSchedule;
use App\Services\ConferenceContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private ConferenceContext $conferenceContext) {}

    public function index(Request $request): View
    {
        $conference = $this->conferenceContext->current();
        $schedules = ProgramSchedule::query()->published()
            ->whereHas('day', fn ($query) => $query->whereBelongsTo($conference))
            ->with('day')->orderBy('conference_day_id')->orderBy('start_time')->get();
        $qrPayload = null;
        $attendanceCode = null;
        $expiresAt = null;
        $selectedSchedule = null;

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'program_schedule_id' => ['nullable', 'integer', Rule::in($schedules->modelKeys())],
            ]);
            $selectedSchedule = $schedules->firstWhere('id', $data['program_schedule_id'] ?? null);
            $expiresAt = now()->addMinutes(15);
            $qrPayload = Crypt::encryptString(json_encode([
                'purpose' => 'attendance',
                'conference_id' => $conference->id,
                'program_schedule_id' => $selectedSchedule?->id,
                'expires_at' => $expiresAt->timestamp,
                'attendance_date' => now($conference->timezone)->toDateString(),
            ], JSON_THROW_ON_ERROR));
            for ($attempt = 0; $attempt < 5; $attempt++) {
                $attendanceCode = strtoupper(bin2hex(random_bytes(4)));
                if (Cache::add('attendance-code:'.$conference->id.':'.$attendanceCode, $qrPayload, 15 * 60)) {
                    break;
                }
                if ($attempt === 4) {
                    throw new \RuntimeException('Unable to store attendance code.');
                }
            }
        }

        return view('admin.attendance', compact('conference', 'schedules', 'qrPayload', 'expiresAt', 'selectedSchedule', 'attendanceCode'));
    }
}
