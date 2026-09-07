@extends('layouts.admin')
@section('title', 'Review Abstract')
@section('content')
    <a href="{{ route('admin.submissions.abstracts') }}" class="btn btn-outline-secondary mb-3">Back to Abstracts</a>
    <h1 class="h3">{{ $submission->title }}</h1>
    <div class="card p-4 mb-4">
        <p>{{ $submission->submission_code }} · {{ $submission->status->label() }}</p>
        <p>{{ $submission->user->name }} · {{ $submission->user->email }}</p>
        <p>{{ $submission->topic?->title }}</p>
        <h2 class="h5">Abstract</h2>
        <div class="mb-3" style="white-space: pre-wrap">{{ $submission->abstract_text ?? 'See the attached abstract file.' }}</div>
        @if ($submission->abstract_file)<a class="btn btn-outline-primary align-self-start mb-3" href="{{ route('admin.submissions.file', $submission) }}">Download Abstract</a>@endif
        <h2 class="h5">Authors</h2>
        @foreach ($submission->authors as $author)<p class="mb-1">{{ $author->name }} — {{ $author->affiliation }} ({{ $author->country }})</p>@endforeach
    </div>
    <form class="card p-4 mb-4" method="POST" action="{{ route('admin.submissions.review.store', $submission) }}" enctype="multipart/form-data">
        @csrf
        <h2 class="h5">Your Review</h2>
        @if ($errors->any())<div class="alert alert-danger">{{ implode(' ', $errors->all()) }}</div>@endif
        <div class="row g-3 mb-3">
            @foreach (['originality' => 'Originality', 'relevance' => 'Relevance', 'quality' => 'Scientific quality'] as $key => $label)
                <div class="col-md-4"><label class="form-label" for="score-{{ $key }}">{{ $label }} (1–5)</label><input type="number" class="form-control" id="score-{{ $key }}" name="scores[{{ $key }}]" min="1" max="5" value="{{ old('scores.'.$key, $review?->scores->firstWhere('criteria', $key)?->score) }}" required></div>
            @endforeach
        </div>
        <label class="form-label" for="author-comments">Comments for author</label>
        <textarea class="form-control mb-3" id="author-comments" name="comments_for_author" maxlength="5000" rows="4">{{ old('comments_for_author', $review?->comments_for_author) }}</textarea>
        <label class="form-label" for="confidential-comments">Confidential comments (committee only)</label>
        <textarea class="form-control mb-3" id="confidential-comments" name="confidential_comments" maxlength="5000">{{ old('confidential_comments', $review?->confidential_comments) }}</textarea>
        <label class="form-label" for="recommendation">Recommendation</label>
        <select class="form-select mb-3" id="recommendation" name="recommendation" required>
            @foreach (\App\Enums\ReviewRecommendation::cases() as $recommendation)<option value="{{ $recommendation->value }}" @selected(old('recommendation', $review?->recommendation?->value) === $recommendation->value)>{{ $recommendation->label() }}</option>@endforeach
        </select>
        <label for="review-attachment" class="form-label">Review attachment (PDF/DOC/DOCX, max 10 MB)</label>
        <input type="file" class="form-control mb-3" id="review-attachment" name="attachment" accept=".pdf,.doc,.docx">
        <button class="btn btn-primary align-self-start">Save Review</button>
    </form>
    <form class="card p-4" method="POST" action="{{ route('admin.submissions.decision', $submission) }}" data-admin-review-form>
        @csrf
        <h2 class="h5">Committee Decision</h2>
        <label for="decision-status" class="form-label">Status</label>
        <select class="form-select mb-3" id="decision-status" name="status">
            @foreach ([\App\Enums\SubmissionStatus::Screening, \App\Enums\SubmissionStatus::UnderReview, \App\Enums\SubmissionStatus::RevisionRequired, \App\Enums\SubmissionStatus::AbstractAccepted, \App\Enums\SubmissionStatus::AbstractRejected] as $status)
                <option value="{{ $status->value }}" @selected($submission->status === $status)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <label for="decision-notes" class="form-label">Decision notes</label>
        <textarea class="form-control mb-3" id="decision-notes" name="notes" maxlength="1000"></textarea>
        <button class="btn btn-primary align-self-start">Save Decision</button>
    </form>
@endsection
