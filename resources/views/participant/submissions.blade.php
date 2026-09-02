@extends('layouts.participant')

@section('heading', 'My Submission')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-black">Abstract submissions</h2>
            <p class="text-sm text-icleh-gray-dark">{{ $conference->name }}</p>
        </div>
        <a href="{{ route('participant.submissions.create') }}" class="rounded-lg bg-icleh-red px-4 py-3 font-bold text-white">Submit Abstract</a>
    </div>
    <div class="icleh-card overflow-x-auto p-4">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="border-b border-black/10 text-icleh-gray-dark">
                <tr>
                    <th class="p-3">Code</th>
                    <th class="p-3">Title</th>
                    <th class="p-3">Topic</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">LoA</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($submissions as $submission)
                    <tr class="border-b border-black/5">
                        <td class="p-3 font-semibold">{{ $submission->submission_code }}</td>
                        <td class="p-3">{{ $submission->title }}</td>
                        <td class="p-3">{{ $submission->topic?->title ?? '-' }}</td>
                        <td class="p-3">{{ $submission->status->label() }}</td>
                        <td class="p-3">{{ $submission->loaDocument ? 'Issued' : '-' }}</td>
                        <td class="p-3 text-right"><a class="font-bold text-icleh-red" href="{{ route('participant.submissions.show', $submission) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-4 text-center text-icleh-gray-dark">No submissions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
