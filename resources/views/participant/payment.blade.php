@extends('layouts.participant')

@section('heading', 'Payment')

@section('content')
    @if (! $registration)
        <div class="card participant-card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div>
                        <h2 class="h5 mb-1">Registration required</h2>
                        <p class="text-secondary mb-0">Choose a registration fee before uploading payment proof.</p>
                    </div>
                    <a href="{{ route('participant.registration') }}" class="btn btn-primary fw-semibold">
                        <i class="ti ti-clipboard-list me-1"></i>Choose Registration
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="row g-3">
            <div class="col-12 col-xl-4">
                <aside class="card participant-card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0">Payment Summary</h2>
                    </div>
                    <div class="card-body">
                        <dl class="participant-summary-list mb-0">
                            <div>
                                <dt class="small text-secondary">Code</dt>
                                <dd class="fw-semibold mb-0 text-break-balanced">{{ $payment?->payment_code ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="small text-secondary">Amount</dt>
                                <dd class="fw-semibold mb-0">{{ $payment?->formattedAmount() ?? $registration->fee?->formattedAmount() ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="small text-secondary">Status</dt>
                                <dd class="mb-0">
                                    <span class="badge text-bg-warning">{{ $payment?->status->label() ?? 'Waiting' }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="small text-secondary">Method</dt>
                                <dd class="mb-0">Manual transfer</dd>
                            </div>
                            @if ($payment?->proof_file)
                                <div>
                                    <dt class="small text-secondary">Proof file</dt>
                                    <dd class="mb-0">
                                        <a href="{{ route('participant.payment.proof') }}" class="btn btn-outline-primary btn-sm fw-semibold mt-1" target="_blank" rel="noopener">
                                            <i class="ti ti-eye me-1"></i>View Proof
                                        </a>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </aside>
            </div>

            <div class="col-12 col-xl-8">
                <form method="POST" action="{{ route('participant.payment.store') }}" enctype="multipart/form-data" class="card participant-card border-0 shadow-sm" data-payment-form>
                    @csrf

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold" for="payment-paid-at">Paid at</label>
                                <input
                                    class="form-control @error('paid_at') is-invalid @enderror"
                                    id="payment-paid-at"
                                    name="paid_at"
                                    type="date"
                                    value="{{ old('paid_at', $payment?->paid_at?->format('Y-m-d')) }}"
                                >
                                @error('paid_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold" for="payment-proof-file-fallback">Proof file</label>
                                <div class="dropzone payment-proof-dropzone @error('proof_file') is-invalid @enderror" data-payment-dropzone>
                                    <div class="dz-message m-0">
                                        <i class="ti ti-cloud-upload fs-2 d-block mb-2"></i>
                                        <span class="fw-semibold d-block">Drop payment proof here</span>
                                        <span class="small text-secondary">PDF, JPG, PNG, or WEBP. Max 4 MB.</span>
                                    </div>
                                </div>
                                <input
                                    class="form-control mt-3 @error('proof_file') is-invalid @enderror"
                                    id="payment-proof-file-fallback"
                                    data-payment-proof-fallback
                                    type="file"
                                    name="proof_file"
                                    accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
                                    required
                                >
                                @error('proof_file')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold" for="payment-notes">Notes</label>
                                <textarea class="form-control" id="payment-notes" name="notes" rows="4">{{ old('notes', $payment?->notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white d-flex justify-content-end">
                        <button class="btn btn-primary fw-semibold" type="submit" data-payment-submit>
                            <i class="ti ti-upload me-1"></i>Submit Payment Proof
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
