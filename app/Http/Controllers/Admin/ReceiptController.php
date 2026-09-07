<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Registration;
use App\Services\ConferenceContext;
use Illuminate\Http\JsonResponse;

class ReceiptController extends Controller
{
    public function __construct(private ConferenceContext $conferenceContext) {}

    public function registration(Registration $registration): JsonResponse
    {
        abort_unless($registration->conference_id === $this->conferenceContext->current()->id, 404);
        $registration->load(['conference', 'user', 'fee', 'payment', 'country']);

        return $this->document('Bukti Pendaftaran', $registration->registration_code, [
            'Konferensi' => $registration->conference->name,
            'Nama peserta' => $registration->user->name,
            'Email' => $registration->user->email,
            'Institusi' => $registration->user->institution,
            'Negara' => $registration->country?->name,
            'Kategori' => $registration->fee?->name,
            'Kehadiran' => $registration->attendance_mode,
            'Tanggal daftar' => $registration->registered_at?->format('d M Y H:i'),
            'Status pendaftaran' => $registration->status->label(),
            'Status pembayaran' => $registration->payment?->status->label() ?? 'Belum ada pembayaran',
        ], 'Dokumen ini adalah bukti pendaftaran, bukan bukti pelunasan pembayaran.');
    }

    public function payment(Payment $payment): JsonResponse
    {
        $payment->load(['registration.conference', 'registration.user', 'registration.fee', 'verifier']);
        abort_unless($payment->registration->conference_id === $this->conferenceContext->current()->id, 404);
        abort_unless($payment->status === PaymentStatus::Verified, 422, 'Payment must be verified before downloading a receipt.');

        return $this->document('Bukti Pembayaran', $payment->payment_code, [
            'Konferensi' => $payment->registration->conference->name,
            'Kode pendaftaran' => $payment->registration->registration_code,
            'Nama peserta' => $payment->registration->user->name,
            'Email' => $payment->registration->user->email,
            'Kategori' => $payment->registration->fee?->name,
            'Jumlah dibayar' => $payment->currency.' '.number_format($payment->amount, 0, ',', '.'),
            'Metode' => $payment->method,
            'Tanggal bayar' => $payment->paid_at?->format('d M Y'),
            'Tanggal verifikasi' => $payment->verified_at?->format('d M Y H:i'),
            'Diverifikasi oleh' => $payment->verifier?->name,
            'Status' => $payment->status->label(),
        ], 'Pembayaran telah diverifikasi oleh panitia ICLEH.');
    }

    /** @param array<string, string|null> $fields */
    private function document(string $title, string $code, array $fields, string $note): JsonResponse
    {
        return response()->json([
            'filename' => $code.'.pdf',
            'definition' => [
                'pageSize' => 'A4',
                'pageMargins' => [45, 50, 45, 50],
                'content' => [
                    ['text' => 'ICLEH 2026', 'fontSize' => 22, 'bold' => true, 'color' => '#9a1825'],
                    ['text' => $title, 'fontSize' => 18, 'bold' => true, 'margin' => [0, 10, 0, 6]],
                    ['text' => $code, 'margin' => [0, 0, 0, 24]],
                    ['table' => ['widths' => [140, '*'], 'body' => collect($fields)->map(fn (?string $value, string $label): array => [['text' => $label, 'bold' => true], $value ?: '-'])->values()->all()], 'layout' => 'lightHorizontalLines'],
                    ['text' => $note, 'fontSize' => 10, 'margin' => [0, 24, 0, 0]],
                ],
                'defaultStyle' => ['fontSize' => 11],
            ],
        ]);
    }
}
