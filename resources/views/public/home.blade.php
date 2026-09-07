@extends('layouts.public')

@section('title', '5th ICLEH 2026 - International Conference on Law, Economy, and Health')

@php
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
    <section class="summit-hero">
        <div class="icleh-container relative z-10 text-center">
            <p class="summit-date">{{ $conference->start_date->format('d M') }} – {{ $conference->end_date->format('d M Y') }} <span aria-hidden="true">·</span> {{ $conference->location }}</p>
            <p class="mt-8 text-sm font-bold uppercase tracking-[0.25em] text-icleh-gold-light">5th ICLEH 2026</p>
            <h1 class="mx-auto mt-5 max-w-5xl text-4xl font-bold leading-tight tracking-tight md:text-6xl">International Conference on<br class="hidden md:block"> Law, Economy, and Health</h1>
            <div class="mx-auto mt-6 max-w-3xl rounded-2xl bg-black/65 px-5 py-5 text-white md:px-8">
                <p class="text-lg font-semibold leading-8">Theme: {{ $conference->theme }}</p>
                <p class="mt-4 text-base leading-7">Connecting research, policy, and practice.<br>Join ICLEH in Semarang or participate online.</p>
            </div>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="{{ route('register') }}" class="landing-button landing-button-primary">Join as Participant <span class="ml-3" aria-hidden="true">↗</span></a>
                <a href="{{ route('participant.submissions.create') }}" class="landing-button border border-white/30 bg-white/10 text-white">Join as Presenter <span class="ml-3" aria-hidden="true">↗</span></a>
            </div>
            <div class="mx-auto mt-12 flex max-w-2xl flex-wrap justify-center gap-3 border-t border-white/20 pt-7 text-xs font-semibold uppercase tracking-widest text-white/80">
                <span>Law</span><span aria-hidden="true">·</span><span>Economy</span><span aria-hidden="true">·</span><span>Health</span><span aria-hidden="true">·</span><span>Artificial Intelligence</span>
            </div>
            <a href="#about" class="mt-10 inline-flex flex-col gap-2 text-xs text-white/70">Explore the conference <span class="text-xl" aria-hidden="true">↓</span></a>
        </div>
    </section>
    <div class="summit-highlights">
        <div class="icleh-container grid grid-cols-2 gap-6 py-8 text-center md:grid-cols-4">
            <div><strong>5th</strong><span>Conference edition</span></div>
            <div><strong>{{ $conference->topics->count() }}</strong><span>Research scopes</span></div>
            <div><strong>{{ $conference->speakers->count() }}</strong><span>Invited speakers</span></div>
            <div><strong>Hybrid</strong><span>Semarang & online</span></div>
        </div>
    </div>

    <div class="summit-content container mx-auto max-w-screen-xl px-4 text-gray-700 lg:px-8">
        <section id="about" class="mt-24 grid items-center gap-10 lg:grid-cols-2">
            <div class="relative">
                <div class="hidden"></div>
                <span class="landing-kicker">About the Conference</span>
                <h2 class="relative z-10 mt-5 text-3xl font-semibold text-darken lg:pr-10">Reimagining law, economy, and health in the age of <span class="text-yellow-500">artificial intelligence.</span></h2>
                <p class="py-5 leading-8 text-gray-500 lg:pr-20">{{ $conference->description }}</p>
                <a href="{{ route('about') }}" class="mt-7 inline-flex font-semibold text-yellow-500 underline">Learn More</a>
            </div>
            <div class="relative">
                <div class="hidden absolute -left-3 -top-3 z-0 size-24 rounded-2xl bg-skilline-cyan"></div>
                <img class="relative z-10 rounded-2xl" src="{{ Vite::asset('resources/images/sample-run.jpg') }}" alt="ICLEH conference participants">
                <div class="hidden absolute -bottom-3 -right-3 z-0 size-40 rounded-2xl bg-yellow-500"></div>
            </div>
        </section>

        <section id="program" class="mt-32">
            <div class="mx-auto max-w-2xl text-center">
                <span class="landing-kicker">Program</span>
                <h2 class="mt-5 text-3xl font-semibold text-darken">Conference Agenda</h2>
                <p class="mt-4 leading-7 text-gray-500">Follow the plenary agenda, chamber schedule, and hybrid venue information for ICLEH 2026.</p>
            </div>
            <div class="mt-10 grid gap-8 lg:grid-cols-1">
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
                                    @forelse ($day->schedules as $schedule)
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
            </div>
        </section>

        <section id="speakers" class="mt-32">
            <div class="mx-auto max-w-2xl text-center">
                <span class="landing-kicker">Keynotes and Speaker</span>
                <h2 class="mt-5 text-3xl font-semibold text-darken">Speakers & Academic Leaders</h2>
                <p class="mt-4 leading-7 text-gray-500">Meet invited academics, practitioners, and institutional leaders joining ICLEH 2026.</p>
            </div>
            <div class="mt-10 grid gap-12" data-speaker-tabs>
                <div class="speaker-tabs" role="tablist" aria-label="Speaker categories">
                    <button class="speaker-tab" id="keynote-tab" type="button" role="tab" aria-selected="true" aria-controls="keynote-panel" tabindex="0">Keynote Speakers</button>
                    <button class="speaker-tab" id="regular-tab" type="button" role="tab" aria-selected="false" aria-controls="regular-panel" tabindex="-1">Speakers</button>
                </div>
                @foreach ([
                    ['id' => 'keynote', 'title' => 'Keynote Speakers', 'speakers' => $keynoteSpeakers, 'columns' => 'md:grid-cols-2'],
                    ['id' => 'regular', 'title' => 'Speakers', 'speakers' => $regularSpeakers, 'columns' => 'md:grid-cols-2 lg:grid-cols-3'],
                ] as $speakerSection)
                        <div id="{{ $speakerSection['id'] }}-panel" role="tabpanel" aria-labelledby="{{ $speakerSection['id'] }}-tab" tabindex="0" @if (! $loop->first) hidden @endif>
                            <h3 class="sr-only">{{ $speakerSection['title'] }}</h3>
                            <div class="grid gap-5 {{ $speakerSection['columns'] }}">
                                @forelse ($speakerSection['speakers'] as $speaker)
                                    <article class="speaker-card rounded-2xl bg-white p-6 shadow-xl">
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
                                @empty
                                    <p class="col-span-full rounded-2xl bg-white p-8 text-center text-gray-500">{{ $speakerSection['title'] }} will be announced soon.</p>
                                @endforelse
                            </div>
                        </div>
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

            <div class="mx-auto mt-8 grid gap-4 md:grid-cols-2">
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

        <section id="important-dates" class="mt-32">
            <div class="text-center">
                <span class="landing-kicker">Mark your calendar</span>
                <h2 class="mt-5 text-3xl font-semibold text-darken">Important Dates</h2>
                <p class="mt-4 text-gray-500">Plan your submission and participation in ICLEH 2026.</p>
            </div>
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($conference->dates as $date)
                    <article class="rounded-2xl border border-gray-200 bg-white p-6">
                        <p class="text-xs font-bold uppercase tracking-widest text-yellow-500">{{ $date->status->label() }}</p>
                        <p class="mt-5 text-3xl font-bold text-darken">{{ $date->starts_at?->format('d M') ?? 'TBA' }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ $date->starts_at?->format('Y') }}</p>
                        <h3 class="mt-5 font-semibold text-darken">{{ $date->name }}</h3>
                        @if ($date->ends_at)
                            <p class="mt-2 text-sm text-gray-500">Until {{ $date->ends_at->format('d M Y') }}</p>
                        @endif
                    </article>
                @empty
                    <p class="text-gray-500">Important dates will be published by the committee.</p>
                @endforelse
            </div>
        </section>

        <section id="participant-journey" class="journey-section mt-32" aria-labelledby="journey-title">
            <div class="mx-auto max-w-3xl text-center">
                <span class="landing-kicker">Your ICLEH journey</span>
                <h2 id="journey-title" class="mt-5 text-3xl font-semibold text-darken">From your first idea to <span class="text-yellow-500">the international stage.</span></h2>
                <p class="mt-4 leading-7 text-gray-500">Follow your path to ICLEH 2026, from exploring research themes to sharing your work and connecting with fellow researchers.</p>
            </div>

            <div class="journey-map">
                <svg class="journey-path" viewBox="0 0 1000 520" fill="none" preserveAspectRatio="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="journey-gradient" x1="0" y1="0" x2="1000" y2="520" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#b71924" />
                            <stop offset="0.5" stop-color="#e2b13c" />
                            <stop offset="1" stop-color="#b71924" />
                        </linearGradient>
                    </defs>
                    <path d="M 167 26 H 875 C 985 26 985 316 875 316 H 167" stroke="url(#journey-gradient)" stroke-width="3" stroke-linecap="round" stroke-dasharray="1 10" />
                </svg>
                <ol class="journey-steps">
                    @foreach ([
                        ['title' => 'Explore the Tracks', 'description' => 'Find your research focus across law, economy, health, and artificial intelligence.', 'route' => 'topics', 'link' => 'Discover research scopes', 'icon' => 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M6.5 3H20v19H6.5A2.5 2.5 0 0 1 4 19.5v-14A2.5 2.5 0 0 1 6.5 3Z M8 7h8 M8 11h6'],
                        ['title' => 'Create Your Account', 'description' => 'Set up your participant profile and choose your conference registration category.', 'route' => 'register', 'link' => 'Start your registration', 'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M16 3a4 4 0 0 1 0 8 M22 21v-2a4 4 0 0 0-3-3.87 M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0'],
                        ['title' => 'Submit Your Abstract', 'description' => 'Prepare your abstract using the author guidelines and submit it through the participant portal.', 'route' => 'guide-for-authors', 'link' => 'Read the author guide', 'icon' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z M14 2v6h6 M8 13h8 M8 17h5'],
                        ['title' => 'Track Your Submission', 'description' => 'Check your submission status and follow updates from the committee in your portal.', 'route' => 'participant.submissions', 'link' => 'Open your submissions', 'icon' => 'M9 11l3 3L22 4 M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
                        ['title' => 'Join the Conference', 'description' => 'Plan your hybrid conference experience and take part in presentations and academic discussions.', 'route' => 'program', 'link' => 'Explore the program', 'icon' => 'M8 2v4 M16 2v4 M3 10h18 M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2 M8 14h2 M14 14h2 M8 18h2'],
                        ['title' => 'Connect & Look Ahead', 'description' => 'Build academic connections and explore the conference publication information for your next steps.', 'route' => 'publication', 'link' => 'View publication information', 'icon' => 'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0 M3 12h18 M12 3a17 17 0 0 1 0 18 17 17 0 0 1 0-18'],
                    ] as $step)
                        <li class="journey-step">
                            <span class="journey-number" aria-hidden="true">{{ $loop->iteration }}</span>
                            <div class="journey-step-content">
                                <span class="journey-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $step['icon'] }}" /></svg>
                                </span>
                                <h3>{{ $step['title'] }}</h3>
                                <p>{{ $step['description'] }}</p>
                                <a href="{{ route($step['route']) }}">{{ $step['link'] }} <span aria-hidden="true">↗</span></a>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('register') }}" class="landing-button landing-button-primary">Begin Your Journey</a>
                <a href="{{ route('participant.submissions.create') }}" class="landing-button border border-yellow-500 text-yellow-500">Submit Your Abstract</a>
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


        <section id="venue" class="my-32">
            <div class="mb-8 text-center">
                <span class="landing-kicker">Meet us in Semarang</span>
                <h2 class="mt-5 text-3xl font-semibold text-darken">Conference Venue</h2>
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
        </section>
        <section class="summit-cta mb-16 rounded-3xl p-8 text-center md:p-14">
            <span class="text-sm font-semibold uppercase tracking-widest text-icleh-gold-light">Be part of ICLEH 2026</span>
            <h2 class="mt-4 text-3xl font-bold text-white md:text-4xl">Bring your research to the conversation.</h2>
            <div class="mt-7 flex flex-wrap justify-center gap-4">
                <a href="{{ route('participant.submissions.create') }}" class="landing-button landing-button-primary">Submit Your Abstract</a>
                <a href="{{ route('contact') }}" class="landing-button border border-white/30 text-white">Contact the Committee</a>
            </div>
        </section>
    </div>
@endsection
