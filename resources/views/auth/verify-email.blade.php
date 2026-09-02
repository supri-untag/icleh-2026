@extends('layouts.auth')

@section('title', 'Verify Email - ICLEH 2026')

@section('content')
    <h1 class="text-2xl font-black">Verify your email</h1>
    <p class="mt-3 text-sm leading-6 text-icleh-gray-dark">A verification link has been sent to your email address. Please verify before opening the participant dashboard.</p>
    <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
        @csrf
        <button class="rounded-lg bg-icleh-red px-4 py-3 font-bold text-white hover:bg-icleh-red-dark">Send Verification Link</button>
    </form>
    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button class="text-sm font-bold text-icleh-gray-dark">Logout</button>
    </form>
@endsection
