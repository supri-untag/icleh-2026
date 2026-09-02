@extends('layouts.auth')

@section('title', 'Register - ICLEH 2026')

@section('content')
    <h1 class="text-2xl font-bold text-darken">Register Account</h1>
    <form method="POST" action="{{ route('register') }}" class="mt-6 grid gap-4">
        @csrf
        <label class="grid gap-2 text-sm font-semibold">
            Full name
            <input name="name" value="{{ old('name') }}" required class="rounded-full border border-black/10 px-4 py-3">
            @error('name') <span class="text-sm text-yellow-500">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-2 text-sm font-semibold">
            Email
            <input name="email" type="email" value="{{ old('email') }}" required class="rounded-full border border-black/10 px-4 py-3">
            @error('email') <span class="text-sm text-yellow-500">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-2 text-sm font-semibold">
            WhatsApp
            <input name="whatsapp" value="{{ old('whatsapp') }}" required class="rounded-full border border-black/10 px-4 py-3">
            @error('whatsapp') <span class="text-sm text-yellow-500">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-2 text-sm font-semibold">
            Institution
            <input name="institution" value="{{ old('institution') }}" required class="rounded-full border border-black/10 px-4 py-3">
            @error('institution') <span class="text-sm text-yellow-500">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-2 text-sm font-semibold">
            Country
            <input name="country" value="{{ old('country', 'Indonesia') }}" required class="rounded-full border border-black/10 px-4 py-3">
            @error('country') <span class="text-sm text-yellow-500">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-2 text-sm font-semibold">
            Password
            <input name="password" type="password" required class="rounded-full border border-black/10 px-4 py-3">
            @error('password') <span class="text-sm text-yellow-500">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-2 text-sm font-semibold">
            Confirm password
            <input name="password_confirmation" type="password" required class="rounded-full border border-black/10 px-4 py-3">
        </label>
        <label class="flex items-start gap-2 text-sm leading-6">
            <input name="consent" type="checkbox" value="1" required class="mt-1 size-4 rounded border-black/20">
            <span>I agree to ICLEH 2026 processing my registration and submission data.</span>
        </label>
        @error('consent') <span class="text-sm text-yellow-500">{{ $message }}</span> @enderror
        <button class="landing-button landing-button-primary">Create Account</button>
    </form>
    <p class="mt-5 text-sm text-gray-500">Already registered? <a class="font-bold text-yellow-500" href="{{ route('login') }}">Login</a>.</p>
@endsection
