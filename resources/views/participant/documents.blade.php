@extends('layouts.participant')

@section('heading', 'Documents')

@section('content')
    <div class="grid gap-6 xl:grid-cols-2">
        <section class="icleh-card p-5">
            <h2 class="text-xl font-black">Letter of Acceptance</h2>
            <div class="mt-4 grid gap-3">
                @forelse (($submissions ?? collect()) as $submission)
                    <div class="rounded-lg bg-icleh-gray p-4">
                        <p class="font-bold">{{ $submission->title }}</p>
                        @if ($submission->loaDocument)
                            <a href="{{ route('participant.loa.show', $submission->loaDocument) }}" class="mt-3 inline-flex font-bold text-icleh-red">Open LoA</a>
                        @else
                            <p class="mt-2 text-sm text-icleh-gray-dark">Not issued yet.</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-icleh-gray-dark">No submission documents yet.</p>
                @endforelse
            </div>
        </section>
        <section class="icleh-card p-5">
            <h2 class="text-xl font-black">Attendance / QR</h2>
            <div class="mt-4 rounded-lg bg-icleh-gray p-5 text-center">
                <p class="font-bold">{{ $registration?->registration_code ?? 'No registration' }}</p>
                <div class="mx-auto mt-4 grid size-40 place-items-center rounded-lg bg-white text-4xl font-black text-icleh-red">QR</div>
            </div>
        </section>
    </div>
@endsection
