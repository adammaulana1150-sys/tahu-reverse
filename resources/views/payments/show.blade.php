@extends('layouts.app')

@section('content')
<main class="wrap payment-page">
    <span class="eyebrow">Pembayaran QRIS</span>
    <h1 class="payment-title">
        @if($payment->status === 'paid')
            Pembayaran terverifikasi.
        @elseif($payment->status === 'pending')
            Selesaikan pesanan Anda.
        @else
            Status pembayaran: {{ ucfirst($payment->status) }}.
        @endif
    </h1>

    <div class="payment-grid">
        <section class="payment-panel">
            <h2>Ringkasan pesanan</h2>
            <p><strong>Referensi:</strong> {{ $payment->reference }}</p>
            <p><strong>Produk:</strong> {{ $payment->order->product->name }} × {{ $payment->order->quantity }}</p>
            <p><strong>Total yang harus dibayar:</strong> Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
            <p><strong>Status pembayaran:</strong> <span class="capitalize">{{ $payment->status }}</span></p>
            <p><strong>Status pesanan:</strong> <span class="capitalize">{{ $payment->order->fulfillment_status }}</span></p>
            @if($payment->status === 'paid')
                <p>Pembayaran telah diperiksa dan dikonfirmasi oleh admin.</p>
            @elseif($payment->status === 'pending' && $qrisImageUrl)
                <p>Setelah membayar, admin akan mencocokkan transaksi di aplikasi merchant resmi. Status berubah setelah pembayaran diverifikasi.</p>
            @endif
        </section>

        <section class="payment-panel">
            <h2>Bayar dengan QRIS</h2>
            @if($payment->status !== 'pending')
                <p>Pembayaran ini tidak lagi menunggu pembayaran atau verifikasi.</p>
            @elseif($qrisImageUrl)
                <img class="payment-qr" src="{{ $qrisImageUrl }}" alt="QRIS resmi Tahu Reverse">
                <ol class="payment-instructions">
                    <li>Buka aplikasi bank atau pembayaran yang mendukung QRIS, lalu pindai kode resmi di atas.</li>
                    <li>Jika diminta, masukkan tepat Rp {{ number_format($payment->amount, 0, ',', '.') }}.</li>
                    <li>Periksa nama merchant dan jumlah sebelum mengonfirmasi pembayaran.</li>
                    <li>Simpan nomor referensi transaksi untuk membantu admin mencocokkan pembayaran.</li>
                </ol>
                <p class="payment-note">QRIS statis mungkin tidak mengisi jumlah secara otomatis. Admin memverifikasi pembayaran melalui aplikasi merchant; mengirim screenshot atau menekan tombol di halaman ini tidak mengubah status pembayaran.</p>
            @else
                <p class="payment-warning">
                    QRIS merchant resmi belum dikonfigurasi. Jangan mengirim pembayaran ke kode lain. Pesanan ini belum dapat dibayar melalui halaman ini.
                </p>
                @if($whatsappUrl)
                    <a class="btn primary payment-contact" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">Hubungi Tahu Reverse</a>
                @endif
            @endif
        </section>
    </div>

    <a class="btn payment-back" href="{{ route('home') }}">Kembali ke halaman utama</a>
</main>
@endsection
