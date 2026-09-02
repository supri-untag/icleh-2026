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
<body class="font-sans antialiased">
    @php
        $isHomePage = request()->routeIs('home');
        $usesHomeHeader = $isHomePage || trim($__env->yieldContent('header_variant', '')) === 'home';
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
        $headerClass = $usesHomeHeader
            ? 'landing-header-home fixed inset-x-0 top-0 z-50 bg-transparent text-white'
            : 'fixed inset-x-0 top-0 z-50 bg-cream text-gray-700 shadow-sm';
        $mobileMenuClass = $usesHomeHeader
            ? 'border border-white/20 bg-white/15 text-white'
            : 'bg-white text-gray-900 shadow-sm';
        $navLinkClass = $usesHomeHeader
            ? 'text-white/85 hover:text-white'
            : 'text-gray-700 hover:text-gray-900';
        $navActiveClass = $usesHomeHeader ? 'text-icleh-gold-light' : 'text-yellow-500';
        $portalButtonClass = $usesHomeHeader
            ? 'ml-5 border border-white/25 bg-white/15 px-9 py-3 text-white'
            : 'landing-button-light ml-5 px-9 py-3';
    @endphp

    <header class="landing-header {{ $headerClass }}" data-public-header>
        <div class="mx-auto flex max-w-screen-xl flex-col px-8 py-4 md:flex-row md:items-center md:justify-between lg:px-12">
            <div class="flex items-center justify-between py-2 md:py-0">
                <a href="{{ route('home') }}" class="landing-header-brand inline-flex h-12 items-center overflow-visible rounded-lg md:h-14">
                    <img class="h-12 w-auto max-w-44 origin-left scale-110 object-contain md:h-14 md:max-w-52" src="{{ $brandLogo }}" alt="ICLEH 2026">
                </a>
                <details class="landing-mobile-menu relative md:hidden">
                    <summary class="landing-header-menu-toggle cursor-pointer list-none rounded-full px-4 py-2 text-sm font-semibold {{ $mobileMenuClass }}">Menu</summary>
                    <nav class="absolute right-0 z-50 mt-3 grid w-72 gap-2 rounded-2xl border border-black/5 bg-white p-3 text-sm font-semibold text-gray-700 shadow-xl">
                        <a class="rounded-xl px-3 py-2 hover:bg-cream" href="{{ route('home') }}">Home</a>
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
                <a class="landing-header-link rounded-lg bg-transparent px-4 py-2 {{ $navLinkClass }} {{ request()->routeIs('home') ? 'landing-header-active '.$navActiveClass : '' }}" href="{{ route('home') }}">Home</a>
                <details class="landing-nav-details">
                    <summary class="landing-header-link landing-nav-summary rounded-lg px-4 py-2 {{ $navLinkClass }} {{ request()->routeIs(...array_keys($conferenceMenu)) ? 'landing-header-active '.$navActiveClass : '' }}">Conference</summary>
                    <div class="landing-dropdown">
                        @foreach ($conferenceMenu as $route => $label)
                            <a class="rounded-xl px-3 py-2 hover:bg-cream" href="{{ route($route) }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </details>
                <details class="landing-nav-details">
                    <summary class="landing-header-link landing-nav-summary rounded-lg px-4 py-2 {{ $navLinkClass }} {{ request()->routeIs(...array_keys($registrationMenu)) ? 'landing-header-active '.$navActiveClass : '' }}">Registration</summary>
                    <div class="landing-dropdown">
                        @foreach ($registrationMenu as $route => $label)
                            <a class="rounded-xl px-3 py-2 hover:bg-cream" href="{{ route($route) }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </details>
                <details class="landing-nav-details">
                    <summary class="landing-header-link landing-nav-summary rounded-lg px-4 py-2 {{ $navLinkClass }} {{ request()->routeIs(...array_keys($updatesMenu)) ? 'landing-header-active '.$navActiveClass : '' }}">Updates</summary>
                    <div class="landing-dropdown">
                        @foreach ($updatesMenu as $route => $label)
                            <a class="rounded-xl px-3 py-2 hover:bg-cream" href="{{ route($route) }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </details>
                @auth
                    <a class="landing-header-portal landing-button {{ $portalButtonClass }}" href="{{ route('participant.dashboard') }}">Portal</a>
                @else
                    <a class="landing-header-portal landing-button {{ $portalButtonClass }}" href="{{ route('login') }}">Portal</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="{{ $usesHomeHeader ? '' : 'pt-20 md:pt-28' }}">
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
