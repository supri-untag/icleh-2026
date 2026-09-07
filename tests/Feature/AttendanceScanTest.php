<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\Conference;
use App\Models\ProgramSchedule;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class AttendanceScanTest extends TestCase
{
    public function test_admin_qr_can_be_scanned_and_repeat_scan_keeps_original_check_in(): void
    {
        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::Admin);
        $user = User::factory()->create();
        $registration = Registration::factory()->for($conference)->for($user)->create();
        $response = $this->actingAs($admin)->post(route('admin.attendance.generate'));
        $response->assertOk()->assertSee('data-attendance-qr', false);
        $code = $response->viewData('qrPayload');

        $this->actingAs($user)->postJson(route('participant.attendance.store'), ['code' => $code])
            ->assertOk()->assertJson(['message' => 'Attendance recorded successfully.']);
        $attendance = $registration->attendances()->firstOrFail();
        $original = $attendance->getAttributes();
        $this->travel(1)->minutes();
        $this->postJson(route('participant.attendance.store'), ['code' => $code])
            ->assertOk()->assertJson(['message' => 'You have already checked in for this session.']);

        $this->assertSame(1, $registration->attendances()->count());
        $this->assertSame($original, $attendance->fresh()->getAttributes());
        $this->assertSame($user->id, $attendance->recorded_by);
        $this->assertSame('qr', $attendance->method->value);
        $this->get(route('participant.attendance'))->assertSee('General attendance')->assertSee('Open Camera');
    }

    public function test_session_qr_records_the_selected_session_and_rejects_unpublished_session(): void
    {
        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        $schedule = ProgramSchedule::query()->published()->whereHas('day', fn ($query) => $query->whereBelongsTo($conference))->firstOrFail();
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::Admin);
        $user = User::factory()->create();
        $registration = Registration::factory()->for($conference)->for($user)->create();
        $response = $this->actingAs($admin)->post(route('admin.attendance.generate'), ['program_schedule_id' => $schedule->id]);
        $response->assertOk();
        $code = $response->viewData('qrPayload');

        $this->actingAs($user)->postJson(route('participant.attendance.store'), ['code' => $code])->assertOk();

        $this->assertSame($schedule->id, $registration->attendances()->firstOrFail()->program_schedule_id);
        $schedule->update(['published' => false]);
        $this->postJson(route('participant.attendance.store'), ['code' => $code])->assertUnprocessable();
        $this->assertSame(1, $registration->attendances()->count());
    }

    public function test_manual_code_and_qr_share_attendance_and_expiration(): void
    {
        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::Admin);
        $user = User::factory()->create();
        $registration = Registration::factory()->for($conference)->for($user)->create();
        $response = $this->actingAs($admin)->post(route('admin.attendance.generate'));
        $response->assertOk();
        $code = $response->viewData('attendanceCode');
        $formattedCode = strtolower(substr($code, 0, 4).'-'.substr($code, 4));

        $this->actingAs($user)->post(route('participant.attendance.store'), ['code' => $formattedCode])
            ->assertRedirect(route('participant.attendance'))->assertSessionHas('status', 'Attendance recorded successfully.');
        $this->assertSame('manual', $registration->attendances()->firstOrFail()->method->value);
        $this->postJson(route('participant.attendance.store'), ['code' => $response->viewData('qrPayload')])
            ->assertOk()->assertJson(['message' => 'You have already checked in for this session.']);
        $this->assertSame(1, $registration->attendances()->count());
        $this->get(route('participant.attendance'))->assertSee('Check In with Code');

        $this->travel(16)->minutes();
        $this->postJson(route('participant.attendance.store'), ['code' => $code])
            ->assertUnprocessable()->assertJsonValidationErrors('code');
        $this->assertSame(1, $registration->attendances()->count());
    }

    public function test_invalid_expired_and_other_conference_codes_do_not_record_attendance(): void
    {
        $user = User::factory()->create();
        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        Registration::factory()->for($conference)->for($user)->create();
        $payload = ['purpose' => 'attendance', 'conference_id' => $conference->id, 'attendance_date' => now($conference->timezone)->toDateString(), 'expires_at' => now()->subMinute()->timestamp];
        $expired = Crypt::encryptString(json_encode($payload));
        $payload['expires_at'] = now()->addMinutes(15)->timestamp;
        $payload['conference_id'] = $conference->id + 100;
        $foreign = Crypt::encryptString(json_encode($payload));

        foreach (['not-a-qr', $expired, $foreign] as $code) {
            $this->actingAs($user)->postJson(route('participant.attendance.store'), ['code' => $code])
                ->assertUnprocessable()->assertJsonValidationErrors('code');
        }
        $this->assertSame(0, Attendance::query()->count());
    }

    public function test_participants_cannot_generate_admin_codes_and_unregistered_users_cannot_check_in(): void
    {
        $user = User::factory()->create();
        $conference = Conference::query()->where('slug', 'icleh-2026')->firstOrFail();
        $code = Crypt::encryptString(json_encode(['purpose' => 'attendance', 'conference_id' => $conference->id, 'attendance_date' => now($conference->timezone)->toDateString(), 'expires_at' => now()->addMinutes(15)->timestamp]));

        $this->actingAs($user)->post(route('admin.attendance.generate'))->assertForbidden();
        $this->postJson(route('participant.attendance.store'), ['code' => $code])->assertUnprocessable()->assertJsonValidationErrors('code');
        $this->assertSame(0, Attendance::query()->count());
    }
}
