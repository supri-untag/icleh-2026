<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>@yield('title', 'Account - ICLEH 2026')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream font-sans text-gray-700">
    <main class="grid min-h-screen items-center px-4 py-10 lg:grid-cols-[1fr_0.9fr] lg:px-12">
        <section class="mx-auto w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <a href="{{ route('home') }}" class="mb-8 inline-flex rounded-lg">
                <img class="h-16 w-auto max-w-56 object-contain" src="{{ Vite::asset('resources/images/LOGO ICLEH.png') }}" alt="ICLEH 2026">
            </a>
            @if (session('status'))
                <div class="mb-4 rounded-2xl bg-yellow-100 px-4 py-3 text-sm text-darken">{{ session('status') }}</div>
            @endif
            @yield('content')
        </section>

        <section class="relative hidden lg:block">
            <img class="floating mx-auto w-10/12" src="{{ Vite::asset('resources/images/bgabout.png') }}" alt="ICLEH conference illustration">
            <div class="absolute left-14 top-16 rounded-2xl bg-white/90 px-5 py-4 shadow-xl">
                <p class="text-sm font-semibold text-yellow-500">ICLEH 2026</p>
                <p class="mt-1 font-bold text-darken">Participant Portal</p>
            </div>
            <div class="absolute bottom-16 right-20 rounded-2xl bg-skilline-indigo px-5 py-4 text-white shadow-xl">
                <p class="text-sm">Registration, payment, submission</p>
            </div>
        </section>
    </main>
</body>
</html>
