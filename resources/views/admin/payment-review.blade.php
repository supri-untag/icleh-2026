@extends('layouts.admin')
@section('title', 'Review Payment')
@section('content')
    <a href="{{ route('admin.participants.payments') }}" class="btn btn-outline-secondary mb-3">Back to Payments</a>
    <button class="btn btn-outline-primary mb-3" data-document-url="{{ route('admin.registrations.receipt', $payment->registration) }}">Unduh Bukti Daftar</button>
    @if ($payment->status === \App\Enums\PaymentStatus::Verified)
        <button class="btn btn-primary mb-3" data-document-url="{{ route('admin.payments.receipt', $payment) }}">Unduh Bukti Bayar</button>
    @endif
    <h1 class="h3">Review Payment — {{ $payment->payment_code }}</h1>
    <div class="card p-4 mb-4">
        <h2 class="h5">{{ $payment->registration->user->name }}</h2>
        <p>{{ $payment->registration->user->email }}</p>
        <p>{{ $payment->registration->fee?->name }} — <strong>{{ $payment->formattedAmount() }}</strong></p>
        <p>Status: <strong>{{ $payment->status->label() }}</strong> · Paid at: {{ $payment->paid_at?->format('d M Y') ?? '-' }}</p>
        <p>{{ $payment->notes }}</p>
        @if ($payment->rejection_reason)<p class="text-danger">{{ $payment->rejection_reason }}</p>@endif
        @if ($payment->proof_file)
            <a class="btn btn-outline-primary align-self-start" href="{{ route('admin.payments.proof', $payment) }}">Download Payment Proof</a>
        @else
            <p class="text-secondary">Payment proof has not been uploaded.</p>
        @endif
    </div>
    @if ($payment->proof_file && $payment->status === \App\Enums\PaymentStatus::Submitted)
        <div class="row g-3">
            <div class="col-md-6">
                <form class="card p-4" method="POST" action="{{ route('admin.payments.verify', $payment) }}" data-admin-review-form>
                    @csrf
                    <label class="form-label" for="verify-notes">Verification notes</label>
                    <textarea class="form-control mb-3" id="verify-notes" name="notes" maxlength="1000"></textarea>
                    <button class="btn btn-success">Verify Payment</button>
                </form>
            </div>
            <div class="col-md-6">
                <form class="card p-4" method="POST" action="{{ route('admin.payments.reject', $payment) }}" data-admin-review-form>
                    @csrf
                    <label class="form-label" for="rejection-reason">Reason for rejection</label>
                    <textarea class="form-control mb-3" id="rejection-reason" name="rejection_reason" maxlength="1000" required></textarea>
                    <button class="btn btn-outline-danger">Reject Payment</button>
                </form>
            </div>
        </div>
    @endif
@endsection
