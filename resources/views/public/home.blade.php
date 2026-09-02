@extends('layouts.public')

@section('title', '5th ICLEH 2026 - International Conference on Law, Economy, and Health')

@php
    $landingImage = fn (string $path): string => asset('images/landing/'.$path);
    $keynoteSpeakers = $conference->speakers
        ->filter(fn ($speaker) => $speaker->type === 'keynote')
        ->values();
    $regularSpeakers = $conference->speakers
        ->reject(fn ($speaker) => $speaker->type === 'keynote')
        ->values();
    $logoPartners = $conference->partners
        ->filter(fn ($partner) => filled($partner->logo))
        ->values();
    $partnerLogoUrl = function (?string $logo): ?string {
        $logo = trim((string) $logo);

        if ($logo === '') {
            return null;
        }

        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://') || str_starts_with($logo, '/')) {
            return $logo;
        }

        return asset($logo);
    };
@endphp

@section('content')
    <section class="landing-hero">
        <div class="mx-auto flex min-h-[720px] max-w-screen-xl items-center px-8 pb-28 pt-20">
            <div class="max-w-3xl text-center lg:text-left">
                <p class="mb-3 text-sm font-bold uppercase tracking-widest text-icleh-gold-light">International Conference</p>
                <h1 class="my-4 text-5xl font-bold leading-tight text-white md:text-7xl">
                    <sup class="text-3xl leading-none md:text-4xl">5th</sup>
                    <span class="text-icleh-gold-light">ICLEH</span> 2026
                </h1>
                <p class="mb-5 text-2xl font-semibold leading-normal text-white">International Conference on Law, Economy, and Health</p>
                <p class="mb-4 max-w-2xl text-lg leading-8 text-white/80">{{ $conference->theme }}</p>
                <p class="mb-8 max-w-2xl font-semibold text-white/85">{{ $conference->start_date->format('d') }}-{{ $conference->end_date->format('d M Y') }} | {{ $conference->venue_name ?? $conference->location }}</p>
                <div class="w-full space-y-4 md:flex md:items-center md:justify-center md:space-x-5 md:space-y-0 lg:justify-start">
                    <a href="{{ route('register') }}" class="landing-button landing-button-primary">Register Now</a>
                    <a href="#program" class="landing-button border border-white/70 text-white">View Program</a>
                    <a href="{{ route('participant.submissions.create') }}" class="inline-flex items-center justify-center gap-3 font-semibold text-white transition hover:scale-105">
                        <span class="flex size-14 items-center justify-center rounded-full border border-white/25 bg-white/15 text-white shadow-lg">
                            <span class="ml-1 block size-0 border-y-[8px] border-l-[12px] border-y-transparent border-l-current"></span>
                        </span>
                        Submit abstract
                    </a>
                </div>
            </div>
        </div>
        <div class="landing-wave">
            <svg class="xl:h-40 xl:w-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z" fill="currentColor"></path>
            </svg>
            <div class="-mt-px h-12 w-full bg-white sm:h-20"></div>
        </div>
    </section>

    <div class="container mx-auto max-w-screen-xl overflow-x-hidden px-4 text-gray-700 lg:px-8">
        <section id="about" class="mt-24 grid items-center gap-10 lg:grid-cols-2">
            <div class="relative">
                <div class="absolute -left-4 -top-3 z-0 size-12 animate-pulse rounded-full bg-yellow-500"></div>
                <span class="landing-kicker">About the Conference</span>
                <h2 class="relative z-10 mt-5 text-3xl font-semibold text-darken lg:pr-10">Reimagining law, economy, and health in the age of <span class="text-yellow-500">artificial intelligence.</span></h2>
                <p class="py-5 leading-8 text-gray-500 lg:pr-20">{{ $conference->description }}</p>
                <a href="{{ route('about') }}" class="mt-7 inline-flex font-semibold text-yellow-500 underline">Learn More</a>
            </div>
            <div class="relative">
                <div class="floating absolute -left-3 -top-3 z-0 size-24 rounded-2xl bg-skilline-cyan"></div>
                <img class="relative z-10 rounded-2xl" src="{{ Vite::asset('resources/images/sample-run.jpg') }}" alt="ICLEH conference participants">
                <div class="floating absolute -bottom-3 -right-3 z-0 size-40 rounded-2xl bg-yellow-500"></div>
            </div>
        </section>

        <section id="speakers" class="mt-32">
            <div class="mx-auto max-w-2xl text-center">
                <span class="landing-kicker">Keynotes and Speaker</span>
                <h2 class="mt-5 text-3xl font-semibold text-darken">Keynote Speakers and Speakers</h2>
                <p class="mt-4 leading-7 text-gray-500">Meet invited academics, practitioners, and institutional leaders joining ICLEH 2026.</p>
            </div>
            <div class="mt-10 grid gap-12">
                @foreach ([
                    ['title' => 'Keynote Speakers', 'speakers' => $keynoteSpeakers, 'columns' => 'md:grid-cols-2'],
                    ['title' => 'Speakers', 'speakers' => $regularSpeakers, 'columns' => 'md:grid-cols-2 lg:grid-cols-3'],
                ] as $speakerSection)
                    @if ($speakerSection['speakers']->isNotEmpty())
                        <div>
                            <h3 class="mb-5 text-2xl font-bold text-darken">{{ $speakerSection['title'] }}</h3>
                            <div class="grid gap-5 {{ $speakerSection['columns'] }}">
                                @foreach ($speakerSection['speakers'] as $speaker)
                                    <article class="rounded-2xl bg-white p-6 shadow-xl">
                                        @php
                                            $speakerPhotoUrl = $speaker->photoUrl();
                                        @endphp
                                        @if ($speakerPhotoUrl)
                                            <img class="mb-5 h-56 w-full rounded-2xl object-cover object-top" src="{{ $speakerPhotoUrl }}" alt="{{ $speaker->name }}">
                                        @else
                                            <div class="mb-5 flex h-56 items-center justify-center rounded-2xl bg-cream text-4xl font-bold text-yellow-500">
                                                {{ mb_substr($speaker->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <p class="text-xs font-bold uppercase tracking-wider text-yellow-500">{{ ucwords(str_replace('_', ' ', $speaker->type)) }}</p>
                                        <h4 class="mt-2 text-xl font-bold text-darken">{{ $speaker->name }}</h4>
                                        <p class="mt-2 text-sm text-gray-500">{{ $speaker->affiliation }}</p>
                                        <p class="mt-1 text-sm text-gray-500">{{ $speaker->country }}</p>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
                <div class="text-center">
                    <a href="{{ route('speakers') }}" class="landing-button border border-yellow-500 text-yellow-500">View All Speakers</a>
                </div>
            </div>
        </section>

        <section id="topics" class="mt-32">
            <div class="mx-auto max-w-3xl text-center text-gray-500">
                <span class="landing-kicker">Conference Scopes</span>
                <h2 class="mt-5 text-3xl font-semibold text-darken">Research scopes for <span class="text-yellow-500">law, economy, health, and AI.</span></h2>
                <p class="mt-4 leading-7">Each scope includes focused subthemes and related keywords curated for ICLEH 2026.</p>
            </div>

            <div class="mx-auto mt-8 grid max-w-4xl gap-4">
                @foreach ($conference->topics as $topic)
                    @php
                        $subthemes = collect($topic->keywords ?? [])->filter()->values();
                    @endphp
                    <details class="group rounded-2xl bg-white p-5 shadow-lg">
                        <summary class="flex cursor-pointer list-none items-center gap-5">
                            <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-cream font-bold text-darken">{{ $loop->iteration }}</span>
                            <span class="flex-1 text-left font-semibold text-darken">{{ $topic->title }}</span>
                            <span class="text-2xl font-semibold text-yellow-500 transition group-open:rotate-45">+</span>
                        </summary>
                        <div class="mt-5 border-t border-gray-100 pt-5">
                            @if ($topic->description)
                                <p class="leading-7 text-gray-500">{{ $topic->description }}</p>
                            @endif

                            @if ($subthemes->isNotEmpty())
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($subthemes as $subtheme)
                                        <span class="rounded-full bg-cream px-4 py-2 text-sm font-semibold text-darken">{{ $subtheme }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">Subthemes will be published by the committee.</p>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('topics') }}" class="font-semibold text-yellow-500 underline">View All Scopes</a>
            </div>
        </section>

        <section id="registration" class="mt-32">
            <div class="grid items-center gap-10 md:grid-cols-2">
                <div class="lg:pl-14">
                    <span class="landing-kicker">Registration</span>
                    <h2 class="mt-5 text-3xl font-semibold text-darken lg:pr-24">Join ICLEH 2026 and share your work on an international stage</h2>
                    <p class="my-4 leading-8 text-gray-500 lg:pr-20">Secure your place in a hybrid forum for researchers, professionals, and policy voices across law, economy, health, and artificial intelligence. Select your category, confirm your registration, and bring your abstract into the ICLEH 2026 program.</p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="landing-button landing-button-primary">Register Now</a>
                        <a href="{{ route('registration') }}" class="landing-button border border-yellow-500 text-yellow-500">View Fees</a>
                    </div>
                </div>
                <img class="mx-auto md:w-10/12" src="{{ Vite::asset('resources/images/bgabout.png') }}" alt="ICLEH conference illustration">
            </div>
            <div class="mt-8 grid gap-5 md:grid-cols-3">
                @foreach ($conference->registrationFees as $fee)
                    <article class="rounded-2xl bg-white p-6 shadow-xl">
                        <h3 class="text-lg font-bold text-darken">{{ $fee->name }}</h3>
                        <p class="mt-3 text-3xl font-bold text-yellow-500">{{ $fee->formattedAmount() }}</p>
                        <p class="mt-4 text-sm leading-6 text-gray-500">{{ $fee->description }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section id="partners" class="mt-32">
            <div class="mx-auto max-w-2xl text-center">
                <span class="landing-kicker">Sponsor & Partner</span>
                <h2 class="mt-5 text-3xl font-semibold text-darken">Sponsors and Partners</h2>
                <p class="mt-4 leading-7 text-gray-500">ICLEH 2026 is supported by organizers, partners, and sponsors committed to academic collaboration.</p>
            </div>

            @if ($logoPartners->isNotEmpty())
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($logoPartners as $partner)
                        @php
                            $logoUrl = $partnerLogoUrl($partner->logo);
                        @endphp

                        @if ($partner->url)
                            <a href="{{ $partner->url }}" target="_blank" rel="noopener" class="flex min-h-32 items-center justify-center rounded-2xl bg-white p-6 shadow-xl" aria-label="{{ $partner->name }}">
                                <img class="h-20 max-w-full object-contain" src="{{ $logoUrl }}" alt="{{ $partner->name }}">
                            </a>
                        @else
                            <div class="flex min-h-32 items-center justify-center rounded-2xl bg-white p-6 shadow-xl">
                                <img class="h-20 max-w-full object-contain" src="{{ $logoUrl }}" alt="{{ $partner->name }}">
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </section>

        <section id="program" class="my-32">
            <div class="mx-auto max-w-2xl text-center">
                <span class="landing-kicker">Program</span>
                <h2 class="mt-5 text-3xl font-semibold text-darken">Schedule and Conference Location</h2>
                <p class="mt-4 leading-7 text-gray-500">Follow the plenary agenda, chamber schedule, and hybrid venue information for ICLEH 2026.</p>
            </div>
            <div class="mt-10 grid gap-8 lg:grid-cols-[1.25fr_0.75fr]">
                <div>
                    <h3 class="mb-5 text-2xl font-bold text-darken">Schedule</h3>
                    <div class="grid gap-5">
                        @forelse ($conference->days as $day)
                            <article class="rounded-2xl bg-white p-6 shadow-xl">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-bold uppercase tracking-wider text-yellow-500">{{ $day->label }}</p>
                                        <h4 class="mt-1 text-xl font-bold text-darken">{{ $day->date->format('d M Y') }}</h4>
                                    </div>
                                    <a href="{{ route('program') }}" class="text-sm font-semibold text-yellow-500 underline">Full Schedule</a>
                                </div>
                                <div class="mt-5 grid gap-3">
                                    @forelse ($day->schedules->take(3) as $schedule)
                                        <div class="rounded-2xl bg-cream p-4">
                                            <p class="font-bold text-darken">{{ $schedule->title }}</p>
                                            <p class="mt-1 text-sm text-gray-500">{{ mb_substr((string) $schedule->start_time, 0, 5) }} - {{ mb_substr((string) $schedule->end_time, 0, 5) }} | {{ $schedule->chamber?->name ?? 'Main Hall' }}</p>
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
                </div>
                <div>
                    <h3 class="mb-5 text-2xl font-bold text-darken">Conference Location</h3>
                    <article class="rounded-2xl bg-white p-6 shadow-xl">
                        <div class="mb-5 overflow-hidden rounded-2xl bg-cream">
                            <iframe
                                class="h-72 w-full border-0 md:h-80"
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d877.4815526222635!2d110.41907666219701!3d-6.9750356798252655!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e70f4acbb52400d%3A0x82996c26f4cdd252!2sUniversitas%2017%20Agustus%201945%20(UNTAG)%20Semarang!5e0!3m2!1sid!2sid!4v1788323034839!5m2!1sid!2sid"
                                title="Universitas 17 Agustus 1945 (UNTAG) Semarang map"
                                allowfullscreen
                                loading="lazy"
                                referrerpolicy="strict-origin-when-cross-origin">
                            </iframe>
                        </div>
                        <h4 class="text-xl font-bold text-darken">{{ $conference->venue?->name ?? $conference->venue_name }}</h4>
                        <p class="mt-3 leading-7 text-gray-500">{{ $conference->venue?->address ?? $conference->location }}</p>
                        @if ($conference->venue?->description)
                            <p class="mt-3 text-sm leading-6 text-gray-500">{{ $conference->venue->description }}</p>
                        @endif
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('venue') }}" class="landing-button border border-yellow-500 text-yellow-500">Venue Detail</a>
                            @if ($conference->venue?->map_url)
                                <a href="{{ $conference->venue->map_url }}" target="_blank" rel="noopener" class="landing-button landing-button-primary">Open Map</a>
                            @endif
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </div>
@endsection
