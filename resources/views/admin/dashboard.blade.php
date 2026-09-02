@extends('layouts.admin')

@section('title', 'Admin Dashboard - ICLEH 2026')

@section('content')
    <div class="mb-4">
        <h1 class="fs-3 mb-1">Dashboard</h1>
        <p class="text-secondary mb-0">{{ $conference->name }} | {{ $conference->start_date->format('d M Y') }} - {{ $conference->end_date->format('d M Y') }}</p>
    </div>

    <div class="row g-3 mb-4">
        @foreach ([
            ['Total Registered', $summary['total_registered'], 'ti-users', 'primary'],
            ['Verified', $summary['total_verified'], 'ti-shield-check', 'success'],
            ['Pending Payments', $summary['pending_payments'], 'ti-credit-card', 'warning'],
            ['Revenue', 'Rp'.number_format((int) $summary['revenue'], 0, ',', '.'), 'ti-cash', 'info'],
            ['Presenters', $summary['presenters'], 'ti-presentation', 'primary'],
            ['Abstracts', $summary['abstracts_submitted'], 'ti-file-text', 'info'],
            ['Accepted', $summary['accepted'], 'ti-file-check', 'success'],
            ['Rejected', $summary['rejected'], 'ti-file-x', 'danger'],
        ] as [$label, $value, $icon, $tone])
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card admin-stat-card p-4 bg-{{ $tone }} bg-opacity-10 border border-{{ $tone }} border-opacity-25 rounded-2">
                    <div class="d-flex gap-3">
                        <div class="icon-shape icon-md bg-{{ $tone }} text-white rounded-2">
                            <i class="ti {{ $icon }} fs-4"></i>
                        </div>
                        <div>
                            <h2 class="mb-3 fs-6">{{ $label }}</h2>
                            <h3 class="fw-bold mb-0">{{ $value }}</h3>
                            <p class="text-{{ $tone }} mb-0 small">ICLEH 2026</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Recent Registrations</h2>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($summary['recent_registrations'] as $registration)
                        <div class="list-group-item d-flex justify-content-between gap-3">
                            <div>
                                <p class="fw-semibold mb-1">{{ $registration->user->name }}</p>
                                <small class="text-secondary">{{ $registration->registration_code }} | {{ $registration->fee?->name }}</small>
                            </div>
                            <span class="badge text-bg-primary align-self-center">{{ $registration->status->label() }}</span>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">No registration yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Recent Submissions</h2>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($summary['recent_submissions'] as $submission)
                        <div class="list-group-item">
                            <p class="fw-semibold mb-1">{{ $submission->title }}</p>
                            <small class="text-secondary">{{ $submission->user->name }} | {{ $submission->topic?->title }}</small>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">No abstract submission yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
