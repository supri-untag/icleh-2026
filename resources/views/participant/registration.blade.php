@extends('layouts.participant')

@section('heading', 'Registration')

@section('content')
    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <form method="POST" action="{{ route('participant.registration.store') }}" class="card participant-card border-0 shadow-sm">
                @csrf

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="registration-fee-id">Fee category</label>
                            <select
                                class="form-select @error('registration_fee_id') is-invalid @enderror"
                                id="registration-fee-id"
                                name="registration_fee_id"
                            >
                                @foreach ($conference->registrationFees as $fee)
                                    <option value="{{ $fee->id }}" data-type="{{ $fee->participant_type }}" @selected((int) old('registration_fee_id', $registration?->registration_fee_id) === $fee->id)>
                                        {{ $fee->name }} - {{ $fee->formattedAmount() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('registration_fee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="registration-participant-type">Participation type</label>
                            <select class="form-select" id="registration-participant-type" name="participant_type">
                                <option value="internal_student" @selected(old('participant_type', $registration?->participant_type) === 'internal_student')>Internal Participant / Student</option>
                                <option value="general" @selected(old('participant_type', $registration?->participant_type) === 'general')>General Participant</option>
                                <option value="presenter" @selected(old('participant_type', $registration?->participant_type) === 'presenter')>Presenter</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="registration-attendance-mode">Attendance mode</label>
                            <select
                                class="form-select @error('attendance_mode') is-invalid @enderror"
                                id="registration-attendance-mode"
                                name="attendance_mode"
                            >
                                <option value="">Participant / not applicable</option>
                                <option value="offline" @selected(old('attendance_mode', $registration?->attendance_mode) === 'offline')>Offline</option>
                                <option value="online" @selected(old('attendance_mode', $registration?->attendance_mode) === 'online')>Online</option>
                            </select>
                            @error('attendance_mode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" for="registration-notes">Notes</label>
                            <textarea class="form-control" id="registration-notes" name="notes" rows="4">{{ old('notes', $registration?->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white d-flex justify-content-end">
                    <button class="btn btn-primary fw-semibold" type="submit">
                        <i class="ti ti-device-floppy me-1"></i>Save Registration
                    </button>
                </div>
            </form>
        </div>

        <div class="col-12 col-xl-4">
            <aside class="card participant-card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Current Status</h2>
                </div>
                <div class="card-body">
                    <dl class="participant-summary-list mb-0">
                        <div>
                            <dt class="small text-secondary">Registration code</dt>
                            <dd class="fw-semibold mb-0 text-break-balanced">{{ $registration?->registration_code ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="small text-secondary">Status</dt>
                            <dd class="mb-0">
                                <span class="badge text-bg-primary">{{ $registration?->status->label() ?? 'Not registered' }}</span>
                            </dd>
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
@endsection
