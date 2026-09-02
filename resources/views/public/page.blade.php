@extends('layouts.public')

@section('title', ($page?->meta_title ?? ucwords(str_replace('-', ' ', $slug))).' - ICLEH 2026')
@section('meta_description', $page?->meta_description ?? $conference->meta_description)
@section('header_variant', 'home')

@section('content')
    <section
        class="relative isolate overflow-hidden bg-darken text-white"
        style="background-image: linear-gradient(90deg, rgb(0 0 0 / 0.72), rgb(0 0 0 / 0.42)), url('{{ Vite::asset('resources/images/banner_page.jpg') }}'); background-position: center; background-size: cover;">
        <div class="mx-auto max-w-screen-xl px-8 py-24 md:py-28">
            <div class="max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-widest text-icleh-gold-light">ICLEH 2026</p>
                <h1 class="mt-4 text-5xl font-bold leading-tight text-white">{{ $page?->title ?? ucwords(str_replace('-', ' ', $slug)) }}</h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-white/85">{{ $conference->theme }}</p>
            </div>
        </div>
    </section>

    <section class="icleh-section bg-white">
        <div class="icleh-container">
            @switch($slug)
                @case('speakers')
                    @php
                        $keynoteSpeakers = $conference->speakers
                            ->filter(fn ($speaker) => $speaker->type === 'keynote')
                            ->values();
                        $regularSpeakers = $conference->speakers
                            ->reject(fn ($speaker) => $speaker->type === 'keynote')
                            ->values();
                    @endphp

                    <div class="grid gap-14">
                        @foreach ([
                            ['eyebrow' => 'Keynote', 'title' => 'Keynote Speakers', 'speakers' => $keynoteSpeakers, 'large' => true],
                            ['eyebrow' => 'Speakers', 'title' => 'Speakers', 'speakers' => $regularSpeakers, 'large' => false],
                        ] as $speakerSection)
                            @if ($speakerSection['speakers']->isNotEmpty())
                                <section>
                                    <div class="mb-6">
                                        <p class="text-sm font-bold uppercase tracking-widest text-yellow-500">{{ $speakerSection['eyebrow'] }}</p>
                                        <h2 class="mt-2 text-3xl font-bold text-darken">{{ $speakerSection['title'] }}</h2>
                                    </div>

                                    <div class="grid gap-5 md:grid-cols-2 {{ $speakerSection['large'] ? '' : 'lg:grid-cols-3' }}">
                                        @foreach ($speakerSection['speakers'] as $speaker)
                                            <article class="rounded-2xl bg-white p-6 shadow-xl">
                                                @php
                                                    $speakerPhotoUrl = $speaker->photoUrl();
                                                @endphp
                                                @if ($speakerPhotoUrl)
                                                    <img
                                                        class="mb-5 h-56 w-full rounded-2xl object-cover object-top"
                                                        src="{{ $speakerPhotoUrl }}"
                                                        alt="{{ $speaker->name }}"
                                                    >
                                                @else
                                                    <div class="mb-5 flex h-56 items-center justify-center rounded-2xl bg-cream text-4xl font-bold text-yellow-500">
                                                        {{ mb_substr($speaker->name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <p class="text-xs font-bold uppercase tracking-wider text-yellow-500">{{ ucwords(str_replace('_', ' ', $speaker->type)) }}</p>
                                                <h3 class="mt-2 text-xl font-bold text-darken">{{ $speaker->name }}</h3>
                                                <p class="mt-2 text-sm text-gray-500">{{ $speaker->affiliation }}</p>
                                                <p class="mt-1 text-sm text-gray-500">{{ $speaker->country }}</p>
                                            </article>
                                        @endforeach
                                    </div>
                                </section>
                            @endif
                        @endforeach
                    </div>
                    @break

                @case('topics')
                    <div class="grid gap-5 md:grid-cols-2">
                        @foreach ($conference->topics as $topic)
                            <article class="rounded-2xl bg-white p-6 shadow-xl">
                                <h2 class="text-xl font-bold text-darken">{{ $topic->title }}</h2>
                                <p class="mt-3 text-sm leading-6 text-gray-500">{{ collect($topic->keywords ?? [])->join(', ') }}</p>
                            </article>
                        @endforeach
                    </div>
                    @break

                @case('important-dates')
                    <div class="grid gap-4">
                        @foreach ($conference->dates as $date)
                            <article class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-5 shadow-lg">
                                <div>
                                    <h2 class="font-bold text-darken">{{ $date->name }}</h2>
                                    <p class="text-sm text-gray-500">{{ $date->starts_at->format('d M Y') }}{{ $date->ends_at ? ' - '.$date->ends_at->format('d M Y') : '' }}</p>
                                </div>
                                <span class="rounded-full bg-yellow-500 px-4 py-2 text-sm font-bold text-white">{{ $date->status->label() }}</span>
                            </article>
                        @endforeach
                    </div>
                    @break

                @case('registration')
                    <div class="grid gap-5 md:grid-cols-3">
                        @foreach ($conference->registrationFees as $fee)
                            <article class="rounded-2xl bg-white p-6 shadow-xl">
                                <h2 class="font-bold text-darken">{{ $fee->name }}</h2>
                                <p class="mt-3 text-3xl font-bold text-yellow-500">{{ $fee->formattedAmount() }}</p>
                                <p class="mt-4 text-sm leading-6 text-gray-500">{{ $fee->description }}</p>
                            </article>
                        @endforeach
                    </div>
                    <a href="{{ route('register') }}" class="landing-button landing-button-primary mt-8">Register Now</a>
                    @break

                @case('program')
                    <div class="grid gap-5">
                        @forelse ($conference->days as $day)
                            <article class="rounded-2xl bg-white p-6 shadow-xl">
                                <h2 class="font-bold text-darken">{{ $day->label }} - {{ $day->date->format('d M Y') }}</h2>
                                <div class="mt-4 grid gap-3">
                                    @forelse ($day->schedules as $schedule)
                                        <div class="rounded-2xl bg-cream p-4">
                                            <p class="font-bold text-darken">{{ $schedule->title }}</p>
                                            <p class="text-sm text-gray-500">{{ mb_substr((string) $schedule->start_time, 0, 5) }} - {{ mb_substr((string) $schedule->end_time, 0, 5) }} | {{ $schedule->chamber?->name ?? 'Main Hall' }}</p>
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-500">Program will be published by the committee.</p>
                                    @endforelse
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl bg-white p-6 text-gray-500 shadow-xl">Program will be published by the committee.</div>
                        @endforelse
                    </div>
                    @break

                @case('venue')
                    <div class="grid items-center gap-10 lg:grid-cols-2">
                        <div>
                            <h2 class="text-3xl font-bold text-darken">{{ $conference->venue?->name ?? $conference->venue_name }}</h2>
                            <p class="mt-4 leading-7 text-gray-500">{{ $conference->venue?->address ?? $conference->location }}</p>
                            <p class="mt-4 leading-7 text-gray-500">{{ $conference->venue?->description }}</p>
                        </div>
                        <img src="{{ $landingImage('teacher-explaining.png') }}" alt="" class="w-full rounded-2xl object-cover shadow-xl">
                    </div>
                    @break

                @case('faq')
                    <div class="grid gap-3">
                        @foreach ($conference->faqs as $faq)
                            <details class="rounded-2xl bg-white p-5 shadow-lg">
                                <summary class="cursor-pointer font-bold text-darken">{{ $faq->question }}</summary>
                                <p class="mt-3 text-gray-500">{{ $faq->answer }}</p>
                            </details>
                        @endforeach
                    </div>
                    @break

                @default
                    @if ($page)
                        <div class="grid gap-8">
                            @foreach ($page->sections as $section)
                                <section class="rounded-2xl bg-white p-6 shadow-xl">
                                    <h2 class="text-2xl font-bold text-darken">{{ $section->title }}</h2>
                                    <p class="mt-3 leading-8 text-gray-500">{{ $section->body }}</p>
                                </section>
                            @endforeach
                        </div>
                    @else
                        <p class="rounded-2xl bg-white p-6 text-lg leading-8 text-gray-500 shadow-xl">Content is managed from the ICLEH CMS and will be published by the committee.</p>
                    @endif
            @endswitch
        </div>
    </section>
@endsection
