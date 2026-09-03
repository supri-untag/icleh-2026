@extends('layouts.participant')

@section('heading', 'My Submission')

@section('actions')
    <a href="{{ route('participant.submissions.create') }}" class="btn btn-primary fw-semibold">
        <i class="ti ti-file-plus me-1"></i>Submit Abstract
    </a>
@endsection

@section('content')
    <div class="card participant-card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h5 mb-1">Abstract submissions</h2>
            <p class="small text-secondary mb-0">{{ $conference->name }}</p>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover participant-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Topic</th>
                            <th>Status</th>
                            <th>LoA</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submissions as $submission)
                            <tr>
                                <td class="fw-semibold text-break-balanced">{{ $submission->submission_code }}</td>
                                <td class="text-break-balanced">{{ $submission->title }}</td>
                                <td>{{ $submission->topic?->title ?? '-' }}</td>
                                <td><span class="badge text-bg-info">{{ $submission->status->label() }}</span></td>
                                <td>{{ $submission->loaDocument ? 'Issued' : '-' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary fw-semibold" href="{{ route('participant.submissions.show', $submission) }}">
                                        <i class="ti ti-external-link me-1"></i>Open
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">No submissions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
