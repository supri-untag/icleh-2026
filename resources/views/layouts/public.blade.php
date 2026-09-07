<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', $conference->meta_description ?? 'ICLEH 2026 Conference Management System')">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>@yield('title', $conference->meta_title ?? 'ICLEH 2026')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-page font-sans antialiased {{ request()->routeIs('home') ? 'summit-page' : '' }}">
    @php
        $isHomePage = request()->routeIs('home');
        $brandLogo = Vite::asset('resources/images/LOGO ICLEH.png');
        $conferenceMenu = [
            'about' => 'About',
            'speakers' => 'Speakers',
            'topics' => 'Topics',
            'important-dates' => 'Important Dates',
            'program' => 'Program',
            'venue' => 'Venue',
            'contact' => 'Contact',
        ];
        $registrationMenu = [
            'registration' => 'Fees & Info',
            'guide-for-authors' => 'Author Guide',
            'register' => 'Create Account',
            'participant.submissions.create' => 'Submit Abstract',
        ];
        $updatesMenu = [
            'publication' => 'Publication',
            'announcements.index' => 'Announcements',
        ];
        $primaryMenu = ['program' => 'Agenda', 'topics' => 'Tracks', 'speakers' => 'Speakers', 'important-dates' => 'Dates', 'venue' => 'Venue'];
    @endphp

    @if ($isHomePage)
        <div class="summit-announcement px-4 py-2 text-center text-xs font-medium">
            5th ICLEH 2026 <span class="mx-2" aria-hidden="true">·</span> {{ $conference->start_date->format('d') }}–{{ $conference->end_date->format('d M Y') }} <span class="mx-2" aria-hidden="true">·</span> Hybrid Conference
            <a href="{{ route('register') }}" class="ml-3 font-bold underline underline-offset-4">Register now ↗</a>
        </div>
    @endif
    <header class="landing-header landing-header-home sticky top-0 z-50 text-white" data-public-header>
        <div class="mx-auto flex max-w-screen-xl flex-col px-8 py-4 md:flex-row md:items-center md:justify-between lg:px-12">
            <div class="flex items-center justify-between py-2 md:py-0">
                <a href="{{ route('home') }}" class="landing-header-brand inline-flex h-12 items-center overflow-visible rounded-lg md:h-14">
                    <img class="h-12 w-auto max-w-44 origin-left scale-110 object-contain md:h-14 md:max-w-52" src="{{ $brandLogo }}" alt="ICLEH 2026">
                </a>
                <details class="landing-mobile-menu relative md:hidden">
                    <summary class="landing-header-menu-toggle cursor-pointer list-none rounded-full px-4 py-2 text-sm font-semibold border border-white/20 bg-white/15 text-white">Menu</summary>
                    <nav class="absolute right-0 z-50 mt-3 grid max-h-[70vh] w-72 gap-2 overflow-y-auto rounded-2xl border border-black/5 bg-white p-3 text-sm font-semibold text-gray-700 shadow-xl">
                        <a class="rounded-xl px-3 py-2 hover:bg-cream" href="{{ route('home') }}">Home</a>
                        @foreach ($primaryMenu as $anchor => $label)
                            <a class="rounded-xl px-3 py-2 hover:bg-cream" href="{{ $isHomePage ? '#'.$anchor : route('home').'#'.$anchor }}">{{ $label }}</a>
                        @endforeach
                        <a class="rounded-xl px-3 py-2 hover:bg-cream" href="{{ route('participant.submissions.create') }}">Paper Submission</a>
                        <details class="rounded-xl px-3 py-2 hover:bg-cream">
                            <summary class="cursor-pointer list-none">Conference</summary>
                            <div class="mt-2 grid gap-1 pl-3 text-gray-500">
                                @foreach ($conferenceMenu as $route => $label)
                                    <a class="rounded-lg px-2 py-1 hover:text-darken" href="{{ route($route) }}">{{ $label }}</a>
                                @endforeach
                            </div>
                        </details>
                        <details class="rounded-xl px-3 py-2 hover:bg-cream">
                            <summary class="cursor-pointer list-none">Registration</summary>
                            <div class="mt-2 grid gap-1 pl-3 text-gray-500">
                                @foreach ($registrationMenu as $route => $label)
                                    <a class="rounded-lg px-2 py-1 hover:text-darken" href="{{ route($route) }}">{{ $label }}</a>
                                @endforeach
                            </div>
                        </details>
                        <details class="rounded-xl px-3 py-2 hover:bg-cream">
                            <summary class="cursor-pointer list-none">Updates</summary>
                            <div class="mt-2 grid gap-1 pl-3 text-gray-500">
                                @foreach ($updatesMenu as $route => $label)
                                    <a class="rounded-lg px-2 py-1 hover:text-darken" href="{{ route($route) }}">{{ $label }}</a>
                                @endforeach
                            </div>
                        </details>
                        @auth
                            <a class="rounded-full bg-yellow-500 px-4 py-2 text-center text-white" href="{{ route('participant.dashboard') }}">Portal</a>
                        @else
                            <a class="rounded-full bg-yellow-500 px-4 py-2 text-center text-white" href="{{ route('login') }}">Portal</a>
                        @endauth
                    </nav>
                </details>
            </div>

            <nav class="landing-nav hidden items-center gap-3 py-3 text-sm md:flex md:justify-end">
                @foreach ($primaryMenu as $anchor => $label)
                    <a href="{{ $isHomePage ? '#'.$anchor : route('home').'#'.$anchor }}" class="landing-header-link rounded-lg px-3 py-2">{{ $label }}</a>
                @endforeach
                <a href="{{ route('participant.submissions.create') }}" class="landing-header-link px-3 py-2">Paper Submission</a>
                @auth
                    <a class="landing-header-portal landing-button ml-5 border border-white/25 bg-white/15 px-9 py-3 text-white" href="{{ route('participant.dashboard') }}">Portal</a>
                @else
                    <a class="landing-header-portal landing-button ml-5 border border-white/25 bg-white/15 px-9 py-3 text-white" href="{{ route('login') }}">Portal</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="mt-24 bg-[#252641] text-white">
        <div class="mx-auto max-w-2xl px-6">
            <div class="flex items-center justify-center px-8 py-12">
                <div class="pr-5">
                    <img class="h-16 w-auto max-w-56 object-contain" src="{{ $brandLogo }}" alt="ICLEH 2026">
                </div>
                <span class="border-l border-gray-500 py-2 pl-5 text-sm font-semibold">International Conference 2026</span>
            </div>
            <div class="pb-10 text-center text-sm text-gray-300">
                <p>11-12 November 2026, Hybrid Conference, Semarang</p>
                <div class="mt-4 flex justify-center gap-4">
                    <a href="{{ route('login') }}">Login</a>
                    <a class="border-l border-gray-500 pl-4" href="{{ route('register') }}">Register</a>
                    <a class="border-l border-gray-500 pl-4" href="{{ route('announcements.index') }}">Announcements</a>
                </div>
                <p class="mt-6 text-gray-400">&copy; {{ now()->year }} ICLEH 2026 Committee</p>
            </div>
        </div>
    </footer>
</body>
</html>
