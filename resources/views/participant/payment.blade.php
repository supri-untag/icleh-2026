@extends('layouts.participant')

@section('heading', 'Payment')

@section('content')
    @if (! $registration)
        <div class="icleh-card p-5">
            <h2 class="text-xl font-black">Registration required</h2>
            <a href="{{ route('participant.registration') }}" class="mt-4 inline-flex rounded-lg bg-icleh-red px-4 py-3 font-bold text-white">Choose Registration</a>
        </div>
    @else
        <div class="grid gap-6 xl:grid-cols-[0.8fr_1fr]">
            <aside class="icleh-card p-5">
                <h2 class="text-xl font-black">Payment Summary</h2>
                <dl class="mt-5 grid gap-3 text-sm">
                    <div><dt class="font-bold">Code</dt><dd>{{ $payment?->payment_code }}</dd></div>
                    <div><dt class="font-bold">Amount</dt><dd>{{ $payment?->formattedAmount() }}</dd></div>
                    <div><dt class="font-bold">Status</dt><dd>{{ $payment?->status->label() }}</dd></div>
                    <div><dt class="font-bold">Method</dt><dd>Manual transfer</dd></div>
                </dl>
            </aside>
            <form method="POST" action="{{ route('participant.payment.store') }}" enctype="multipart/form-data" class="icleh-card grid gap-4 p-5">
                @csrf
                <label class="grid gap-2 text-sm font-semibold">
                    Paid at
                    <input name="paid_at" type="date" value="{{ old('paid_at') }}" class="rounded-lg border border-black/10 px-3 py-3">
                    @error('paid_at') <span class="text-sm text-icleh-red">{{ $message }}</span> @enderror
                </label>
                <label class="grid gap-2 text-sm font-semibold">
                    Proof file
                    <input type="file" name="proof_file" required class="rounded-lg border border-black/10 px-3 py-3">
                    @error('proof_file') <span class="text-sm text-icleh-red">{{ $message }}</span> @enderror
                </label>
                <label class="grid gap-2 text-sm font-semibold">
                    Notes
                    <textarea name="notes" rows="4" class="rounded-lg border border-black/10 px-3 py-3">{{ old('notes') }}</textarea>
                </label>
                <button class="rounded-lg bg-icleh-red px-4 py-3 font-bold text-white">Submit Payment Proof</button>
            </form>
        </div>
    @endif
@endsection
