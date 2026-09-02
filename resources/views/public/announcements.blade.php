@extends('layouts.public')

@section('title', 'Announcements - ICLEH 2026')

@section('content')
    <section
        class="relative isolate overflow-hidden bg-darken text-white"
        style="background-image: linear-gradient(90deg, rgb(0 0 0 / 0.72), rgb(0 0 0 / 0.42)), url('{{ Vite::asset('resources/images/banner_page.jpg') }}'); background-position: center; background-size: cover;">
        <div class="mx-auto max-w-screen-xl px-8 py-24 md:py-28">
            <p class="text-sm font-bold uppercase tracking-widest text-icleh-gold-light">ICLEH 2026</p>
            <h1 class="mt-4 text-5xl font-bold text-white">Announcements</h1>
            <p class="mt-5 max-w-2xl text-lg leading-8 text-white/85">Updates, notices, and publication from the ICLEH 2026 committee.</p>
        </div>
    </section>

    <section class="icleh-section bg-white">
        <div class="icleh-container grid gap-5">
            @forelse ($announcements as $announcement)
                <article class="rounded-2xl bg-white p-6 shadow-xl">
                    <p class="text-sm font-semibold text-yellow-500">{{ $announcement->published_at?->format('d M Y') }}</p>
                    <h2 class="mt-2 text-2xl font-bold text-darken">{{ $announcement->title }}</h2>
                    <p class="mt-3 text-gray-500">{{ $announcement->excerpt }}</p>
                    <a href="{{ route('announcements.show', $announcement->slug) }}" class="mt-4 inline-flex font-bold text-yellow-500">Read announcement</a>
                </article>
            @empty
                <div class="rounded-2xl bg-white p-6 text-gray-500 shadow-xl">No announcements have been published.</div>
            @endforelse

            {{ $announcements->links() }}
        </div>
    </section>
@endsection
