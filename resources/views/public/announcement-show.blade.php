@extends('layouts.public')

@section('title', $announcement->title.' - ICLEH 2026')

@section('content')
    <section
        class="relative isolate overflow-hidden bg-darken text-white"
        style="background-image: linear-gradient(90deg, rgb(0 0 0 / 0.72), rgb(0 0 0 / 0.42)), url('{{ Vite::asset('resources/images/banner_page.jpg') }}'); background-position: center; background-size: cover;">
        <div class="mx-auto max-w-screen-xl px-8 py-24 md:py-28">
            <p class="text-sm font-bold uppercase tracking-widest text-icleh-gold-light">{{ $announcement->published_at?->format('d M Y') }}</p>
            <h1 class="mt-4 max-w-4xl text-5xl font-bold leading-tight text-white">{{ $announcement->title }}</h1>
        </div>
    </section>

    <article class="icleh-section bg-white">
        <div class="icleh-container max-w-4xl rounded-2xl bg-white p-6 text-lg leading-8 text-gray-500 shadow-xl">
            {!! nl2br(e($announcement->body)) !!}
        </div>
    </article>
@endsection
