@extends('layouts.auth')

@section('title', 'Login - ICLEH 2026')

@section('content')
    <h1 class="text-2xl font-bold text-darken">Login</h1>
    <form method="POST" action="{{ route('login') }}" class="mt-6 grid gap-4">
        @csrf
        <label class="grid gap-2 text-sm font-semibold">
            Email
            <input name="email" type="email" value="{{ old('email') }}" required autofocus class="rounded-full border border-black/10 px-4 py-3">
            @error('email') <span class="text-sm text-yellow-500">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-2 text-sm font-semibold">
            Password
            <input name="password" type="password" required class="rounded-full border border-black/10 px-4 py-3">
            @error('password') <span class="text-sm text-yellow-500">{{ $message }}</span> @enderror
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input name="remember" type="checkbox" value="1" class="size-4 rounded border-black/20">
            Remember me
        </label>
        <button class="landing-button landing-button-primary">Login</button>
    </form>
    <p class="mt-5 text-sm text-gray-500">New participant? <a class="font-bold text-yellow-500" href="{{ route('register') }}">Register here</a>.</p>
@endsection
