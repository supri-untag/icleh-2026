@extends('layouts.admin')

@section('title', 'Attendance QR - ICLEH 2026')

@section('content')
    <h1 class="fs-3">Attendance QR</h1>
    <p class="text-secondary">Display this QR for participants to scan from their Attendance page.</p>
    <div class="card p-4">
        <form method="POST" action="{{ route('admin.attendance.generate') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-8">
                <label for="attendance-session" class="form-label">Session</label>
                <select id="attendance-session" name="program_schedule_id" class="form-select">
                    <option value="">General attendance</option>
                    @foreach ($schedules as $schedule)
                        <option value="{{ $schedule->id }}" @selected((int) old('program_schedule_id', $selectedSchedule?->id) === $schedule->id)>{{ $schedule->day->date->format('d M') }} — {{ $schedule->title }}</option>
                    @endforeach
                </select>
                @error('program_schedule_id')<div class="text-danger">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4"><button class="btn btn-primary w-100">Generate QR & Code</button></div>
        </form>
        @if ($qrPayload)
            <div class="text-center mt-4">
                <h2 class="h5">{{ $selectedSchedule?->title ?? 'General attendance' }}</h2>
                <p class="mb-1">Attendance code</p>
                <p class="display-5 fw-bold font-monospace user-select-all">{{ substr($attendanceCode, 0, 4) }}-{{ substr($attendanceCode, 4) }}</p>
                <p class="small text-secondary">Participants can type this code instead of scanning.</p>
                <canvas data-attendance-qr="{{ $qrPayload }}" class="mw-100" aria-label="Attendance QR code"></canvas>
                <p class="text-secondary" data-qr-status>Valid until {{ $expiresAt->timezone($conference->timezone)->format('H:i') }} ({{ $conference->timezone }}). Generate a new QR after it expires.</p>
            </div>
        @endif
    </div>
@endsection
