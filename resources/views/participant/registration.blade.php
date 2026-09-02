@extends('layouts.participant')

@section('heading', 'Registration')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[1fr_0.8fr]">
        <form method="POST" action="{{ route('participant.registration.store') }}" class="icleh-card grid gap-4 p-5">
            @csrf
            <label class="grid gap-2 text-sm font-semibold">
                Fee category
                <select name="registration_fee_id" class="rounded-lg border border-black/10 px-3 py-3">
                    @foreach ($conference->registrationFees as $fee)
                        <option value="{{ $fee->id }}" data-type="{{ $fee->participant_type }}" @selected((int) old('registration_fee_id', $registration?->registration_fee_id) === $fee->id)>
                            {{ $fee->name }} - {{ $fee->formattedAmount() }}
                        </option>
                    @endforeach
                </select>
                @error('registration_fee_id') <span class="text-sm text-icleh-red">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                Participation type
                <select name="participant_type" class="rounded-lg border border-black/10 px-3 py-3">
                    <option value="internal_student" @selected(old('participant_type', $registration?->participant_type) === 'internal_student')>Internal Participant / Student</option>
                    <option value="general" @selected(old('participant_type', $registration?->participant_type) === 'general')>General Participant</option>
                    <option value="presenter" @selected(old('participant_type', $registration?->participant_type) === 'presenter')>Presenter</option>
                </select>
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                Attendance mode
                <select name="attendance_mode" class="rounded-lg border border-black/10 px-3 py-3">
                    <option value="">Participant / not applicable</option>
                    <option value="offline" @selected(old('attendance_mode', $registration?->attendance_mode) === 'offline')>Offline</option>
                    <option value="online" @selected(old('attendance_mode', $registration?->attendance_mode) === 'online')>Online</option>
                </select>
                @error('attendance_mode') <span class="text-sm text-icleh-red">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-2 text-sm font-semibold">
                Notes
                <textarea name="notes" rows="4" class="rounded-lg border border-black/10 px-3 py-3">{{ old('notes', $registration?->notes) }}</textarea>
            </label>
            <button class="rounded-lg bg-icleh-red px-4 py-3 font-bold text-white">Save Registration</button>
        </form>
        <aside class="icleh-card p-5">
            <h2 class="text-xl font-black">Current Status</h2>
            <dl class="mt-5 grid gap-3 text-sm">
                <div><dt class="font-bold">Registration code</dt><dd>{{ $registration?->registration_code ?? '-' }}</dd></div>
                <div><dt class="font-bold">Status</dt><dd>{{ $registration?->status->label() ?? 'Not registered' }}</dd></div>
                <div><dt class="font-bold">Payment</dt><dd>{{ $registration?->payment?->status->label() ?? '-' }}</dd></div>
            </dl>
        </aside>
    </div>
@endsection
