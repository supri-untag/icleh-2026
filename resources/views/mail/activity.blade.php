<!DOCTYPE html>
<html lang="en">
<body style="font-family:Arial,sans-serif;color:#263238;line-height:1.6">
    <h1 style="color:#9a1825;font-size:24px">ICLEH 2026</h1>
    <p>Dear {{ $data['participant_name'] ?? 'Participant' }},</p>
    <h2 style="font-size:18px">{{ $data['activity'] ?? $subject }}</h2>
    <p>{{ $data['conference_name'] ?? 'ICLEH 2026' }}</p>
    @foreach (['registration_code' => 'Registration', 'submission_code' => 'Submission code', 'submission_title' => 'Submission', 'payment_amount' => 'Amount', 'status' => 'Status', 'recommendation' => 'Review recommendation', 'comments_for_author' => 'Comments for author', 'notes' => 'Notes', 'rejection_reason' => 'Reason', 'attendance_date' => 'Attendance date', 'session_title' => 'Session', 'loa_number' => 'LoA number'] as $key => $label)
        @if (filled($data[$key] ?? null))<p><strong>{{ $label }}:</strong><br>{{ $data[$key] }}</p>@endif
    @endforeach
    <p><a href="{{ $data['action_url'] ?? route('participant.dashboard') }}">Open ICLEH portal</a></p>
    <p>ICLEH 2026 Committee</p>
</body>
</html>
