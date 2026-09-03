@extends('layouts.participant')

@section('heading', $submission ? 'Submission Detail' : 'Submit Abstract')

@section('content')
    @if (! $submission)
        @if (! $registration || ! $registration->isPresenter())
            <div class="card participant-card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div>
                            <h2 class="h5 mb-1">Presenter registration required</h2>
                            <p class="text-secondary mb-0">Please choose Presenter as your participation type before submitting an abstract.</p>
                        </div>
                        <a href="{{ route('participant.registration') }}" class="btn btn-primary fw-semibold">
                            <i class="ti ti-clipboard-list me-1"></i>Open Registration
                        </a>
                    </div>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('participant.submissions.store') }}" enctype="multipart/form-data" class="card participant-card border-0 shadow-sm">
                @csrf

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="submission-topic">Topic</label>
                            <select
                                class="form-select @error('conference_topic_id') is-invalid @enderror"
                                id="submission-topic"
                                name="conference_topic_id"
                            >
                                @foreach ($conference->topics as $topic)
                                    <option value="{{ $topic->id }}" @selected((int) old('conference_topic_id') === $topic->id)>{{ $topic->title }}</option>
                                @endforeach
                            </select>
                            @error('conference_topic_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" for="submission-title">Title</label>
                            <input
                                class="form-control @error('title') is-invalid @enderror"
                                id="submission-title"
                                name="title"
                                value="{{ old('title') }}"
                                required
                            >
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" for="submission-abstract-text">Abstract text</label>
                            <textarea
                                class="form-control @error('abstract_text') is-invalid @enderror"
                                id="submission-abstract-text"
                                name="abstract_text"
                                rows="6"
                            >{{ old('abstract_text') }}</textarea>
                            @error('abstract_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="submission-keywords">Keywords</label>
                            <input
                                class="form-control @error('keywords') is-invalid @enderror"
                                id="submission-keywords"
                                name="keywords"
                                value="{{ old('keywords') }}"
                                placeholder="AI governance, human rights"
                            >
                            @error('keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="submission-country">Country</label>
                            <input
                                class="form-control @error('country') is-invalid @enderror"
                                id="submission-country"
                                name="country"
                                value="{{ old('country', auth()->user()->country) }}"
                            >
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="submission-corresponding-author">Corresponding author</label>
                            <input
                                class="form-control @error('corresponding_author') is-invalid @enderror"
                                id="submission-corresponding-author"
                                name="corresponding_author"
                                value="{{ old('corresponding_author', auth()->user()->name) }}"
                            >
                            @error('corresponding_author')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="submission-abstract-file">Abstract file</label>
                            <input
                                class="form-control @error('abstract_file') is-invalid @enderror"
                                id="submission-abstract-file"
                                type="file"
                                name="abstract_file"
                            >
                            @error('abstract_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" for="submission-affiliations">Affiliations</label>
                            <textarea
                                class="form-control @error('affiliations') is-invalid @enderror"
                                id="submission-affiliations"
                                name="affiliations"
                                rows="3"
                            >{{ old('affiliations', auth()->user()->institution) }}</textarea>
                            @error('affiliations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <fieldset class="border rounded p-3">
                                <legend class="float-none w-auto px-2 h6 mb-0">Co-author</legend>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="submission-author-name">Name</label>
                                        <input class="form-control" id="submission-author-name" name="authors[0][name]" value="{{ old('authors.0.name') }}" placeholder="Co-author name">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="submission-author-email">Email</label>
                                        <input class="form-control" id="submission-author-email" name="authors[0][email]" value="{{ old('authors.0.email') }}" placeholder="Co-author email">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="submission-author-affiliation">Affiliation</label>
                                        <input class="form-control" id="submission-author-affiliation" name="authors[0][affiliation]" value="{{ old('authors.0.affiliation') }}" placeholder="Affiliation">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="submission-author-country">Country</label>
                                        <input class="form-control" id="submission-author-country" name="authors[0][country]" value="{{ old('authors.0.country') }}" placeholder="Country">
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" for="submission-notes">Notes</label>
                            <textarea class="form-control" id="submission-notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white d-flex justify-content-end">
                    <button class="btn btn-primary fw-semibold" type="submit">
                        <i class="ti ti-send me-1"></i>Submit Abstract
                    </button>
                </div>
            </form>
        @endif
    @else
        <div class="row g-3">
            <div class="col-12 col-xl-8">
                <section class="card participant-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <p class="fw-bold text-primary mb-2">{{ $submission->submission_code }}</p>
                        <h2 class="h3 fw-bold mb-2 text-break-balanced">{{ $submission->title }}</h2>
                        <p class="text-secondary mb-4">{{ $submission->topic?->title }}</p>

                        <dl class="participant-summary-list mb-0">
                            <div>
                                <dt class="small text-secondary">Status</dt>
                                <dd class="mb-0"><span class="badge text-bg-info">{{ $submission->status->label() }}</span></dd>
                            </div>
                            <div>
                                <dt class="small text-secondary">Keywords</dt>
                                <dd class="mb-0 text-break-balanced">{{ collect($submission->keywords ?? [])->join(', ') ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="small text-secondary">Submitted</dt>
                                <dd class="mb-0">{{ $submission->submitted_at?->format('d M Y H:i') ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>
            </div>

            <div class="col-12 col-xl-4">
                <aside class="card participant-card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h3 class="h5 mb-0">Documents</h3>
                    </div>
                    <div class="card-body d-grid gap-2">
                        @if ($submission->loaDocument)
                            <a href="{{ route('participant.loa.show', $submission->loaDocument) }}" class="btn btn-primary fw-semibold">
                                <i class="ti ti-file-certificate me-1"></i>Open LoA
                            </a>
                            <a href="{{ route('verify.loa', $submission->loaDocument->verification_code) }}" class="btn btn-outline-primary fw-semibold">
                                <i class="ti ti-shield-check me-1"></i>Verify LoA
                            </a>
                        @else
                            <p class="text-secondary mb-0">LoA will appear after acceptance.</p>
                        @endif
                    </div>
                </aside>
            </div>
        </div>

        <section class="card participant-card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h3 class="h5 mb-0">Authors</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse ($submission->authors as $author)
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100">
                                <p class="fw-bold mb-1 text-break-balanced">{{ $author->name }}</p>
                                <p class="small text-secondary mb-0 text-break-balanced">{{ $author->affiliation }} {{ $author->country ? '- '.$author->country : '' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-secondary mb-0">No co-authors listed.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    @endif
@endsection
