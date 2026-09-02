@extends('layouts.participant')

@section('heading', $submission ? 'Submission Detail' : 'Submit Abstract')

@section('content')
    @if (! $submission)
        @if (! $registration || ! $registration->isPresenter())
            <div class="icleh-card p-5">
                <h2 class="text-xl font-black">Presenter registration required</h2>
                <p class="mt-2 text-icleh-gray-dark">Please choose Presenter as your participation type before submitting an abstract.</p>
                <a href="{{ route('participant.registration') }}" class="mt-4 inline-flex rounded-lg bg-icleh-red px-4 py-3 font-bold text-white">Open Registration</a>
            </div>
        @else
            <form method="POST" action="{{ route('participant.submissions.store') }}" enctype="multipart/form-data" class="icleh-card grid gap-4 p-5">
                @csrf
                <label class="grid gap-2 text-sm font-semibold">
                    Topic
                    <select name="conference_topic_id" class="rounded-lg border border-black/10 px-3 py-3">
                        @foreach ($conference->topics as $topic)
                            <option value="{{ $topic->id }}" @selected((int) old('conference_topic_id') === $topic->id)>{{ $topic->title }}</option>
                        @endforeach
                    </select>
                    @error('conference_topic_id') <span class="text-sm text-icleh-red">{{ $message }}</span> @enderror
                </label>
                <label class="grid gap-2 text-sm font-semibold">
                    Title
                    <input name="title" value="{{ old('title') }}" required class="rounded-lg border border-black/10 px-3 py-3">
                    @error('title') <span class="text-sm text-icleh-red">{{ $message }}</span> @enderror
                </label>
                <label class="grid gap-2 text-sm font-semibold">
                    Abstract text
                    <textarea name="abstract_text" rows="6" class="rounded-lg border border-black/10 px-3 py-3">{{ old('abstract_text') }}</textarea>
                </label>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm font-semibold">
                        Keywords
                        <input name="keywords" value="{{ old('keywords') }}" placeholder="AI governance, human rights" class="rounded-lg border border-black/10 px-3 py-3">
                    </label>
                    <label class="grid gap-2 text-sm font-semibold">
                        Country
                        <input name="country" value="{{ old('country', auth()->user()->country) }}" class="rounded-lg border border-black/10 px-3 py-3">
                    </label>
                </div>
                <label class="grid gap-2 text-sm font-semibold">
                    Corresponding author
                    <input name="corresponding_author" value="{{ old('corresponding_author', auth()->user()->name) }}" class="rounded-lg border border-black/10 px-3 py-3">
                </label>
                <label class="grid gap-2 text-sm font-semibold">
                    Affiliations
                    <textarea name="affiliations" rows="3" class="rounded-lg border border-black/10 px-3 py-3">{{ old('affiliations', auth()->user()->institution) }}</textarea>
                </label>
                <fieldset class="grid gap-3 rounded-lg border border-black/10 p-4">
                    <legend class="px-2 text-sm font-bold">Co-author</legend>
                    <div class="grid gap-4 md:grid-cols-2">
                        <input name="authors[0][name]" value="{{ old('authors.0.name') }}" placeholder="Co-author name" class="rounded-lg border border-black/10 px-3 py-3">
                        <input name="authors[0][email]" value="{{ old('authors.0.email') }}" placeholder="Co-author email" class="rounded-lg border border-black/10 px-3 py-3">
                        <input name="authors[0][affiliation]" value="{{ old('authors.0.affiliation') }}" placeholder="Affiliation" class="rounded-lg border border-black/10 px-3 py-3">
                        <input name="authors[0][country]" value="{{ old('authors.0.country') }}" placeholder="Country" class="rounded-lg border border-black/10 px-3 py-3">
                    </div>
                </fieldset>
                <label class="grid gap-2 text-sm font-semibold">
                    Abstract file
                    <input type="file" name="abstract_file" class="rounded-lg border border-black/10 px-3 py-3">
                    @error('abstract_file') <span class="text-sm text-icleh-red">{{ $message }}</span> @enderror
                </label>
                <label class="grid gap-2 text-sm font-semibold">
                    Notes
                    <textarea name="notes" rows="3" class="rounded-lg border border-black/10 px-3 py-3">{{ old('notes') }}</textarea>
                </label>
                <button class="rounded-lg bg-icleh-red px-4 py-3 font-bold text-white">Submit Abstract</button>
            </form>
        @endif
    @else
        <div class="grid gap-6 xl:grid-cols-[1fr_0.8fr]">
            <section class="icleh-card p-5">
                <p class="font-bold text-icleh-red">{{ $submission->submission_code }}</p>
                <h2 class="mt-2 text-3xl font-black">{{ $submission->title }}</h2>
                <p class="mt-3 text-icleh-gray-dark">{{ $submission->topic?->title }}</p>
                <dl class="mt-5 grid gap-3 text-sm">
                    <div><dt class="font-bold">Status</dt><dd>{{ $submission->status->label() }}</dd></div>
                    <div><dt class="font-bold">Keywords</dt><dd>{{ collect($submission->keywords ?? [])->join(', ') ?: '-' }}</dd></div>
                    <div><dt class="font-bold">Submitted</dt><dd>{{ $submission->submitted_at?->format('d M Y H:i') }}</dd></div>
                </dl>
            </section>
            <aside class="icleh-card p-5">
                <h3 class="text-xl font-black">Documents</h3>
                <div class="mt-4 grid gap-3">
                    @if ($submission->loaDocument)
                        <a href="{{ route('participant.loa.show', $submission->loaDocument) }}" class="rounded-lg bg-icleh-red px-4 py-3 text-center font-bold text-white">Open LoA</a>
                        <a href="{{ route('verify.loa', $submission->loaDocument->verification_code) }}" class="rounded-lg border border-black/10 px-4 py-3 text-center font-bold">Verify LoA</a>
                    @else
                        <p class="text-sm text-icleh-gray-dark">LoA will appear after acceptance.</p>
                    @endif
                </div>
            </aside>
        </div>

        <section class="icleh-card mt-6 p-5">
            <h3 class="text-xl font-black">Authors</h3>
            <div class="mt-4 grid gap-3">
                @foreach ($submission->authors as $author)
                    <div class="rounded-lg bg-icleh-gray p-4">
                        <p class="font-bold">{{ $author->name }}</p>
                        <p class="text-sm text-icleh-gray-dark">{{ $author->affiliation }} {{ $author->country ? '- '.$author->country : '' }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
