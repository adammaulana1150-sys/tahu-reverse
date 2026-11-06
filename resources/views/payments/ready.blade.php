@extends('layouts.app')

@section('content')
<main class="wrap payment-page">
    <section class="payment-ready-card">
        <span class="eyebrow">Pesanan berhasil dibuat</span>
        @if($qrisReady && $payment->status === 'pending')
            <h1 class="payment-title">QRIS siap. Satu ketukan lagi.</h1>
            <p>Kode QRIS resmi untuk pesanan Anda sudah siap. Tekan tombol di bawah untuk melihat kode dan menyelesaikan pembayaran.</p>
            <a class="btn primary payment-ready-action" href="{{ route('payments.show', ['token' => $token]) }}">Tampilkan QRIS</a>
        @else
            <h1 class="payment-title">Pesanan Anda sudah siap.</h1>
            <p>
                @if($payment->status !== 'pending')
                    Pembayaran pesanan ini tidak lagi menunggu pembayaran.
                @else
                    QRIS merchant resmi belum tersedia saat ini. Buka halaman pembayaran untuk melihat informasi selanjutnya.
                @endif
            </p>
            <a class="btn primary payment-ready-action" href="{{ route('payments.show', ['token' => $token]) }}">Lihat status pembayaran</a>
        @endif

        <div class="payment-ready-summary">
            <p><strong>Referensi:</strong> {{ $payment->reference }}</p>
            <p><strong>Total:</strong> Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
        </div>
    </section>
</main>
@endsection
