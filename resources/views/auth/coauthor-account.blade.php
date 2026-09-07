@extends('layouts.auth')

@section('title', 'Set Password - ICLEH 2026')

@section('content')
    <h1 class="text-2xl font-bold text-darken">Set your password</h1>
    <form method="POST" action="{{ route('coauthor.account.update') }}" class="mt-6 grid gap-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label class="grid gap-2 text-sm font-semibold">Email
            <input type="email" name="email" value="{{ old('email', $email) }}" required class="rounded-full border border-black/10 px-4 py-3">
            @error('email')<span class="text-sm text-yellow-500">{{ $message }}</span>@enderror
        </label>
        <label class="grid gap-2 text-sm font-semibold">Password
            <input type="password" name="password" autocomplete="new-password" required class="rounded-full border border-black/10 px-4 py-3">
            @error('password')<span class="text-sm text-yellow-500">{{ $message }}</span>@enderror
        </label>
        <label class="grid gap-2 text-sm font-semibold">Confirm password
            <input type="password" name="password_confirmation" autocomplete="new-password" required class="rounded-full border border-black/10 px-4 py-3">
        </label>
        @error('token')<span class="text-sm text-yellow-500">{{ $message }}</span>@enderror
        <button class="landing-button landing-button-primary">Save password</button>
    </form>
    <form method="POST" action="{{ route('coauthor.account.resend') }}" class="mt-5">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <button class="text-sm font-semibold text-yellow-500 underline">Link expired? Send a new link</button>
    </form>
@endsection
