@extends('layouts.public')

@section('title', 'Verify Certificate - ICLEH 2026')

@section('content')
    <section class="icleh-section bg-icleh-gray">
        <div class="icleh-container max-w-3xl">
            <div class="icleh-card p-6">
                <p class="font-bold text-icleh-red">Certificate Verification</p>
                @if ($certificate)
                    <h1 class="mt-3 text-3xl font-black text-icleh-black">Valid Certificate</h1>
                    <dl class="mt-6 grid gap-4 text-sm">
                        <div><dt class="font-bold">Certificate Number</dt><dd>{{ $certificate->certificate_number }}</dd></div>
                        <div><dt class="font-bold">Recipient</dt><dd>{{ $certificate->recipient_name }}</dd></div>
                        <div><dt class="font-bold">Type</dt><dd>{{ $certificate->type->label() }}</dd></div>
                        <div><dt class="font-bold">Conference</dt><dd>{{ $certificate->conference->name }}</dd></div>
                        <div><dt class="font-bold">Issued Date</dt><dd>{{ $certificate->issued_date?->format('d M Y') ?? '-' }}</dd></div>
                    </dl>
                @else
                    <h1 class="mt-3 text-3xl font-black text-icleh-black">Document Not Found</h1>
                    <p class="mt-3 text-icleh-gray-dark">Verification code {{ $code }} is not registered.</p>
                @endif
            </div>
        </div>
    </section>
@endsection
