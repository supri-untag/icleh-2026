@extends('layouts.participant')

@section('heading', 'My Profile')

@section('content')
    <form method="POST" action="{{ route('participant.profile.update') }}" enctype="multipart/form-data" class="card participant-card border-0 shadow-sm">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div class="row g-3">
                @foreach ([
                    'full_name' => 'Full name',
                    'whatsapp' => 'WhatsApp',
                    'institution' => 'Institution',
                    'country' => 'Country',
                ] as $field => $label)
                    @php
                        $inputId = 'profile-'.$field;
                        $value = old($field, $user->profile?->{$field} ?? ($field === 'full_name' ? $user->name : $user->{$field}));
                    @endphp

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="{{ $inputId }}">{{ $label }}</label>
                        <input
                            class="form-control @error($field) is-invalid @enderror"
                            id="{{ $inputId }}"
                            name="{{ $field }}"
                            value="{{ $value }}"
                        >
                        @error($field)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" for="profile-participant-type">Participation type</label>
                    <select
                        class="form-select @error('participant_type') is-invalid @enderror"
                        id="profile-participant-type"
                        name="participant_type"
                    >
                        @foreach (['internal_student' => 'Internal / Student', 'general' => 'General Participant', 'presenter' => 'Presenter'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('participant_type', $user->profile?->participant_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('participant_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold" for="profile-attendance-mode">Attendance mode</label>
                    <select
                        class="form-select @error('attendance_mode') is-invalid @enderror"
                        id="profile-attendance-mode"
                        name="attendance_mode"
                    >
                        <option value="">Not selected</option>
                        <option value="offline" @selected(old('attendance_mode', $user->profile?->attendance_mode) === 'offline')>Offline</option>
                        <option value="online" @selected(old('attendance_mode', $user->profile?->attendance_mode) === 'online')>Online</option>
                    </select>
                    @error('attendance_mode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" for="profile-status-proof">Student/Internal proof</label>
                    <input
                        class="form-control @error('status_proof_file') is-invalid @enderror"
                        id="profile-status-proof"
                        type="file"
                        name="status_proof_file"
                    >
                    @error('status_proof_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card-footer bg-white d-flex justify-content-end">
            <button class="btn btn-primary fw-semibold" type="submit">
                <i class="ti ti-device-floppy me-1"></i>Save Profile
            </button>
        </div>
    </form>
@endsection
