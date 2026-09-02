@extends('layouts.public')

@section('title', 'Verify LoA - ICLEH 2026')

@section('content')
    <section class="icleh-section bg-icleh-gray">
        <div class="icleh-container max-w-3xl">
            <div class="icleh-card p-6">
                <p class="font-bold text-icleh-red">LoA Verification</p>
                @if ($loa)
                    <h1 class="mt-3 text-3xl font-black text-icleh-black">Valid Letter of Acceptance</h1>
                    <dl class="mt-6 grid gap-4 text-sm">
                        <div><dt class="font-bold">LoA Number</dt><dd>{{ $loa->loa_number }}</dd></div>
                        <div><dt class="font-bold">Participant</dt><dd>{{ $loa->submission->user->name }}</dd></div>
                        <div><dt class="font-bold">Title</dt><dd>{{ $loa->submission->title }}</dd></div>
                        <div><dt class="font-bold">Conference</dt><dd>{{ $loa->submission->conference->name }}</dd></div>
                        <div><dt class="font-bold">Issued Date</dt><dd>{{ $loa->issued_date->format('d M Y') }}</dd></div>
                    </dl>
                @else
                    <h1 class="mt-3 text-3xl font-black text-icleh-black">Document Not Found</h1>
                    <p class="mt-3 text-icleh-gray-dark">Verification code {{ $code }} is not registered.</p>
                @endif
            </div>
        </div>
    </section>
@endsection
