<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>{{ $loa->loa_number }} - ICLEH 2026</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-icleh-gray p-6 font-sans text-icleh-black">
    <main class="mx-auto max-w-4xl rounded-lg bg-white p-10 shadow-xl">
        <div class="border-b border-black/10 pb-6 text-center">
            <p class="font-bold text-icleh-red">Faculty of Law, Universitas 17 Agustus 1945 Semarang</p>
            <h1 class="mt-3 text-3xl font-black">Letter of Acceptance</h1>
            <p class="mt-2 text-sm text-icleh-gray-dark">{{ $loa->loa_number }}</p>
        </div>
        <section class="py-8 text-lg leading-8">
            <p>Dear {{ $loa->submission->user->name }},</p>
            <p class="mt-4">We are pleased to inform you that your abstract entitled <strong>{{ $loa->submission->title }}</strong> has been accepted for presentation at {{ $loa->submission->conference->name }}.</p>
            <p class="mt-4">The conference will be held on 11-12 November 2026 in hybrid format at Faculty of Law, Universitas 17 Agustus 1945 Semarang.</p>
        </section>
        <section class="grid gap-8 border-t border-black/10 pt-6 md:grid-cols-2">
            <div>
                <p class="text-sm font-bold text-icleh-gray-dark">Issued date</p>
                <p class="font-bold">{{ $loa->issued_date->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-sm font-bold text-icleh-gray-dark">Verification</p>
                <a class="font-bold text-icleh-red" href="{{ route('verify.loa', $loa->verification_code) }}">{{ route('verify.loa', $loa->verification_code) }}</a>
            </div>
        </section>
    </main>
</body>
</html>
