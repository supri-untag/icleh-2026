@extends('layouts.participant')

@section('heading', 'My Profile')

@section('content')
    <form method="POST" action="{{ route('participant.profile.update') }}" enctype="multipart/form-data" class="icleh-card grid max-w-3xl gap-4 p-5">
        @csrf
        @method('PUT')
        @foreach ([
            'full_name' => 'Full name',
            'whatsapp' => 'WhatsApp',
            'institution' => 'Institution',
            'country' => 'Country',
        ] as $field => $label)
            <label class="grid gap-2 text-sm font-semibold">
                {{ $label }}
                <input name="{{ $field }}" value="{{ old($field, $user->profile?->{$field} ?? ($field === 'full_name' ? $user->name : $user->{$field})) }}" class="rounded-lg border border-black/10 px-3 py-3">
                @error($field) <span class="text-sm text-icleh-red">{{ $message }}</span> @enderror
            </label>
        @endforeach
        <div class="grid gap-4 md:grid-cols-2">
            <label class="grid gap-2 text-sm font-semibold">
                Participation type
                <select name="participant_type" class="rounded-lg border border-black/10 px-3 py-3">
                    @foreach (['internal_student' => 'Internal / Student', 'general' => 'General Participant', 'presenter' => 'Presenter'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('participant_type', $user->profile?->participant_type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                Attendance mode
                <select name="attendance_mode" class="rounded-lg border border-black/10 px-3 py-3">
                    <option value="">Not selected</option>
                    <option value="offline" @selected(old('attendance_mode', $user->profile?->attendance_mode) === 'offline')>Offline</option>
                    <option value="online" @selected(old('attendance_mode', $user->profile?->attendance_mode) === 'online')>Online</option>
                </select>
            </label>
        </div>
        <label class="grid gap-2 text-sm font-semibold">
            Student/Internal proof
            <input type="file" name="status_proof_file" class="rounded-lg border border-black/10 px-3 py-3">
            @error('status_proof_file') <span class="text-sm text-icleh-red">{{ $message }}</span> @enderror
        </label>
        <button class="rounded-lg bg-icleh-red px-4 py-3 font-bold text-white">Save Profile</button>
    </form>
@endsection
