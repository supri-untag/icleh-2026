@extends('layouts.participant')

@section('title', 'Participant Dashboard - ICLEH 2026')
@section('heading', 'Dashboard')

@section('content')
    <div class="row g-3 mb-4">
        @foreach ([
            ['Registration status', $registration?->status->label() ?? 'Not registered', 'ti-clipboard-list', 'primary'],
            ['Payment status', $registration?->payment?->status->label() ?? 'Waiting registration', 'ti-credit-card', 'warning'],
            ['Submission status', $submission?->status->label() ?? 'No submission', 'ti-file-text', 'info'],
            ['LoA status', $submission?->loaDocument ? 'Issued' : 'Not issued', 'ti-file-certificate', 'success'],
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
        <div class="col-xl-7">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Participant Journey</h2>
                </div>
                <div class="list-group list-group-flush">
                    @foreach (['Register account', 'Complete profile', 'Choose registration fee', 'Upload payment proof', 'Submit abstract', 'Review and LoA', 'Conference attendance', 'Certificate'] as $item)
                        <div class="list-group-item d-flex align-items-center gap-3">
                            <span class="icon-shape bg-primary text-white rounded-2 fw-bold">{{ $loop->iteration }}</span>
                            <span class="fw-semibold">{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Next Actions</h2>
                </div>
                <div class="card-body d-flex flex-column align-items-stretch gap-2">
                    <a class="btn btn-sm btn-primary fw-semibold" href="{{ route('participant.registration') }}">Manage Registration</a>
                    <a class="btn btn-sm btn-outline-primary fw-semibold" href="{{ route('participant.payment') }}">Upload Payment Proof</a>
                    <a class="btn btn-sm btn-outline-primary fw-semibold" href="{{ route('participant.submissions.create') }}">Submit Abstract</a>
                </div>
            </section>
        </div>
    </div>
@endsection
