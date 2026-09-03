@extends('layouts.participant')

@php
    $participantPage = match (true) {
        request()->routeIs('participant.program') => 'program',
        request()->routeIs('participant.attendance') => 'attendance',
        request()->routeIs('participant.certificates') => 'certificates',
        request()->routeIs('participant.notifications') => 'notifications',
        default => 'loa',
    };

    $pageHeading = match ($participantPage) {
        'program' => 'Conference Program',
        'attendance' => 'Attendance / QR',
        'certificates' => 'Certificates',
        'notifications' => 'Notifications',
        default => 'Letter of Acceptance',
    };
@endphp

@section('heading', $pageHeading)

@section('content')
    @switch($participantPage)
        @case('program')
            <div class="row g-3">
                <div class="col-12 col-xl-8">
                    <div class="card participant-card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h2 class="h5 mb-1">Schedule</h2>
                            <p class="small text-secondary mb-0">{{ $conference->name }}</p>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="participant-program-days">
                                @forelse ($conference->days as $day)
                                    <div class="accordion-item">
                                        <h3 class="accordion-header">
                                            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#program-day-{{ $day->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="program-day-{{ $day->id }}">
                                                <span class="fw-semibold">{{ $day->label }}</span>
                                                <span class="ms-2 text-secondary">{{ $day->date->format('d M Y') }}</span>
                                            </button>
                                        </h3>
                                        <div id="program-day-{{ $day->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#participant-program-days">
                                            <div class="accordion-body d-grid gap-3">
                                                @forelse ($day->schedules as $schedule)
                                                    <div class="border rounded p-3">
                                                        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
                                                            <div>
                                                                <p class="fw-bold mb-1 text-break-balanced">{{ $schedule->title }}</p>
                                                                <p class="small text-secondary mb-0">
                                                                    {{ mb_substr((string) $schedule->start_time, 0, 5) }} - {{ mb_substr((string) $schedule->end_time, 0, 5) }}
                                                                    | {{ $schedule->chamber?->name ?? 'Main Hall' }}
                                                                </p>
                                                            </div>
                                                            <span class="badge text-bg-light border">{{ $schedule->type?->label() ?? 'Session' }}</span>
                                                        </div>
                                                        @if ($schedule->speaker || $schedule->submission)
                                                            <p class="small text-secondary mb-0 text-break-balanced">
                                                                {{ $schedule->speaker?->name ?? $schedule->submission?->corresponding_author }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <p class="text-secondary mb-0">Program will be published by the committee.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-secondary mb-0">Program will be published by the committee.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <aside class="card participant-card border-0 shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h2 class="h5 mb-0">Venue</h2>
                        </div>
                        <div class="card-body">
                            <dl class="participant-summary-list mb-0">
                                <div>
                                    <dt class="small text-secondary">Location</dt>
                                    <dd class="fw-semibold mb-0 text-break-balanced">{{ $conference->venue?->name ?? $conference->venue_name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="small text-secondary">Address</dt>
                                    <dd class="mb-0 text-break-balanced">{{ $conference->venue?->address ?? $conference->location ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="small text-secondary">Mode</dt>
                                    <dd class="mb-0 text-capitalize">{{ $conference->mode ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </aside>
                </div>
            </div>
            @break

        @case('attendance')
            <div class="row g-3">
                <div class="col-12 col-xl-4">
                    <section class="card participant-card border-0 shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h2 class="h5 mb-0">Attendance QR</h2>
                        </div>
                        <div class="card-body text-center">
                            <p class="fw-semibold mb-3 text-break-balanced">{{ $registration?->registration_code ?? 'No registration' }}</p>
                            <div class="participant-qr-box d-flex align-items-center justify-content-center rounded border bg-white text-primary fw-bold fs-1">
                                QR
                            </div>
                            <p class="small text-secondary mt-3 mb-0">Use this registration code during committee check-in.</p>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-xl-8">
                    <section class="card participant-card border-0 shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h2 class="h5 mb-0">Attendance Records</h2>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Session</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse (($registration?->attendances ?? collect()) as $attendance)
                                            <tr>
                                                <td>{{ $attendance->attendance_date?->format('d M Y') ?? '-' }}</td>
                                                <td class="text-break-balanced">{{ $attendance->schedule?->title ?? 'General attendance' }}</td>
                                                <td>{{ $attendance->checked_in_at?->format('H:i') ?? '-' }}</td>
                                                <td>{{ $attendance->checked_out_at?->format('H:i') ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-secondary py-4">No attendance records yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            @break

        @case('certificates')
            <div class="card participant-card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-1">Issued Certificates</h2>
                    <p class="small text-secondary mb-0">{{ $conference->name }}</p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse ($certificates as $certificate)
                            <div class="col-12 col-lg-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                                        <div>
                                            <p class="fw-bold mb-1 text-break-balanced">{{ $certificate->certificate_number }}</p>
                                            <p class="small text-secondary mb-0">{{ $certificate->type?->label() ?? 'Certificate' }}</p>
                                        </div>
                                        <span class="badge text-bg-primary">{{ $certificate->status->label() }}</span>
                                    </div>
                                    <dl class="participant-summary-list mb-3">
                                        <div>
                                            <dt class="small text-secondary">Recipient</dt>
                                            <dd class="mb-0 text-break-balanced">{{ $certificate->recipient_name }}</dd>
                                        </div>
                                        <div>
                                            <dt class="small text-secondary">Issued date</dt>
                                            <dd class="mb-0">{{ $certificate->issued_date?->format('d M Y') ?? '-' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="small text-secondary">Related submission</dt>
                                            <dd class="mb-0 text-break-balanced">{{ $certificate->submission?->title ?? '-' }}</dd>
                                        </div>
                                    </dl>
                                    <a href="{{ route('verify.certificate', $certificate->verification_code) }}" class="btn btn-outline-primary btn-sm fw-semibold">
                                        <i class="ti ti-shield-check me-1"></i>Verify
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-secondary mb-0">Certificates will appear here after they are issued by the committee.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @break

        @case('notifications')
            <div class="card participant-card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-1">Portal Updates</h2>
                    <p class="small text-secondary mb-0">Latest status from your ICLEH 2026 portal.</p>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex gap-3">
                        <span class="icon-shape bg-primary text-white rounded-2 flex-shrink-0">
                            <i class="ti ti-clipboard-check fs-4"></i>
                        </span>
                        <div>
                            <p class="fw-semibold mb-1">Registration</p>
                            <p class="text-secondary mb-0">{{ $registration?->status->label() ?? 'You have not completed registration yet.' }}</p>
                        </div>
                    </div>
                    <div class="list-group-item d-flex gap-3">
                        <span class="icon-shape bg-warning text-white rounded-2 flex-shrink-0">
                            <i class="ti ti-credit-card fs-4"></i>
                        </span>
                        <div>
                            <p class="fw-semibold mb-1">Payment</p>
                            <p class="text-secondary mb-0">{{ $registration?->payment?->status->label() ?? 'Payment status is not available yet.' }}</p>
                        </div>
                    </div>
                    <div class="list-group-item d-flex gap-3">
                        <span class="icon-shape bg-info text-white rounded-2 flex-shrink-0">
                            <i class="ti ti-file-text fs-4"></i>
                        </span>
                        <div>
                            <p class="fw-semibold mb-1">Submissions</p>
                            <p class="text-secondary mb-0">{{ $submissions->count() }} abstract submission(s) recorded.</p>
                        </div>
                    </div>
                    <div class="list-group-item d-flex gap-3">
                        <span class="icon-shape bg-success text-white rounded-2 flex-shrink-0">
                            <i class="ti ti-certificate fs-4"></i>
                        </span>
                        <div>
                            <p class="fw-semibold mb-1">Certificates</p>
                            <p class="text-secondary mb-0">{{ $certificates->count() }} certificate(s) available.</p>
                        </div>
                    </div>
                </div>
            </div>
            @break

        @default
            <div class="row g-3">
                <div class="col-12 col-xl-8">
                    <section class="card participant-card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h2 class="h5 mb-1">Letter of Acceptance</h2>
                            <p class="small text-secondary mb-0">Accepted submissions and LoA documents.</p>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @forelse (($submissions ?? collect()) as $submission)
                                    <div class="col-12">
                                        <div class="border rounded p-3">
                                            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                                                <div>
                                                    <p class="fw-bold mb-1 text-break-balanced">{{ $submission->title }}</p>
                                                    <p class="small text-secondary mb-0">{{ $submission->submission_code }}</p>
                                                </div>
                                                @if ($submission->loaDocument)
                                                    <a href="{{ route('participant.loa.show', $submission->loaDocument) }}" class="btn btn-primary btn-sm fw-semibold">
                                                        <i class="ti ti-file-certificate me-1"></i>Open LoA
                                                    </a>
                                                @else
                                                    <span class="badge text-bg-light border">Not issued yet</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <p class="text-secondary mb-0">No submission documents yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-xl-4">
                    <aside class="card participant-card border-0 shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h2 class="h5 mb-0">Registration</h2>
                        </div>
                        <div class="card-body">
                            <dl class="participant-summary-list mb-0">
                                <div>
                                    <dt class="small text-secondary">Code</dt>
                                    <dd class="fw-semibold mb-0 text-break-balanced">{{ $registration?->registration_code ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="small text-secondary">Status</dt>
                                    <dd class="mb-0">{{ $registration?->status->label() ?? 'Not registered' }}</dd>
                                </div>
                                <div>
                                    <dt class="small text-secondary">Payment</dt>
                                    <dd class="mb-0">{{ $registration?->payment?->status->label() ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </aside>
                </div>
            </div>
    @endswitch
@endsection
