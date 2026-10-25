@extends('layouts.admin')

@section('title', 'Detail Pesanan')
@section('heading', 'Pesanan '.$order->order_code)

@section('content')
<div class="grid gap-5 lg:grid-cols-2">
    <section class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Ringkasan pesanan</h2>
        <dl class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-stone-500">Produk</dt><dd>{{ $order->product->name }} × {{ $order->quantity }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-stone-500">Harga satuan</dt><dd>Rp {{ number_format($order->unit_price, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-stone-500">Total</dt><dd class="font-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-stone-500">Status pembayaran</dt><dd class="capitalize">{{ $order->payment?->status ?? 'legacy' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-stone-500">Metode</dt><dd>{{ $order->payment?->method ?? 'Tidak tersedia' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-stone-500">Referensi pembayaran</dt><dd>{{ $order->payment?->reference ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-stone-500">Status pemenuhan</dt><dd class="capitalize">{{ $order->fulfillment_status }}</dd></div>
            @if($order->payment?->reviewed_at)
                <div class="flex justify-between gap-4"><dt class="text-stone-500">Referensi verifikasi</dt><dd>{{ $order->payment->verification_reference }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-stone-500">Diverifikasi oleh</dt><dd>{{ $order->payment->verifier?->name ?? 'Admin tidak tersedia' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-stone-500">Waktu verifikasi</dt><dd>{{ $order->payment->reviewed_at?->format('d M Y H:i') }}</dd></div>
            @endif
            @if($order->payment?->rejection_reason)
                <div class="flex justify-between gap-4"><dt class="text-stone-500">Alasan penolakan</dt><dd class="max-w-sm text-right">{{ $order->payment->rejection_reason }}</dd></div>
            @endif
            <div class="flex justify-between gap-4"><dt class="text-stone-500">Catatan</dt><dd class="max-w-sm text-right">{{ $order->note ?: '—' }}</dd></div>
        </dl>
    </section>
    <section class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Pelanggan</h2>
        <dl class="mt-4 space-y-3 text-sm">
            <div><dt class="text-stone-500">Nama</dt><dd>{{ $order->customer->name }}</dd></div>
            <div><dt class="text-stone-500">Telepon</dt><dd>{{ $order->customer->phone }}</dd></div>
            <div><dt class="text-stone-500">Alamat</dt><dd>{{ $order->customer->address }}</dd></div>
        </dl>
    </section>
</div>

@if($order->payment?->status === 'pending')
    <section class="mt-5 rounded-xl border border-amber-300 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Verifikasi pembayaran QRIS</h2>
        <p class="mt-2 text-sm text-stone-600">Periksa transaksi masuk di aplikasi merchant resmi dan cocokkan jumlah Rp {{ number_format($order->payment->amount, 0, ',', '.') }} dengan referensi pembayaran sebelum menyetujui.</p>
        @if($qrisImageUrl && $order->payment->method === 'qris_static')
            <img class="mt-4 h-32 w-32 rounded-lg border border-stone-200 object-contain p-2" src="{{ $qrisImageUrl }}" alt="QRIS merchant yang digunakan pelanggan">
        @endif
        <form class="mt-4 space-y-3 border-t border-stone-200 pt-4" method="POST" action="{{ route('admin.orders.payment.verify', $order) }}" onsubmit="return confirm('Saya sudah mencocokkan pembayaran masuk dengan referensi dan jumlah pesanan ini. Verifikasi pembayaran?')">
            @csrf
            <label class="block text-sm font-medium" for="verification_reference">Referensi transaksi dari aplikasi merchant</label>
            <input class="w-full rounded-lg border-stone-300" id="verification_reference" name="verification_reference" required maxlength="120">
            @error('verification_reference')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
            @error('confirm')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
            @error('payment')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
            <label class="flex items-start gap-2 text-sm">
                <input class="mt-1" type="checkbox" name="confirm" value="1" required>
                Saya telah memeriksa pembayaran aktual di aplikasi merchant resmi dan memastikan jumlahnya cocok.
            </label>
            <button class="rounded-lg bg-emerald-700 px-4 py-2 font-medium text-white" type="submit">Konfirmasi pembayaran</button>
        </form>

        <form class="mt-5 space-y-3 border-t border-stone-200 pt-4" method="POST" action="{{ route('admin.orders.payment.reject', $order) }}" onsubmit="return confirm('Tolak klaim pembayaran untuk pesanan ini?')">
            @csrf
            <label class="block text-sm font-medium" for="rejection_reason">Alasan penolakan</label>
            <textarea class="w-full rounded-lg border-stone-300" id="rejection_reason" name="reason" required maxlength="1000" rows="2"></textarea>
            @error('reason')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
            <label class="flex items-start gap-2 text-sm">
                <input class="mt-1" type="checkbox" name="confirm" value="1" required>
                Saya telah memeriksa dan pembayaran ini tidak cocok atau tidak ditemukan.
            </label>
            <button class="rounded-lg border border-red-300 px-4 py-2 font-medium text-red-700 hover:bg-red-50" type="submit">Tolak pembayaran</button>
        </form>
    </section>
@endif

<section class="mt-5 rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
    <h2 class="font-semibold">Status pemenuhan pesanan</h2>
    @if($order->fulfillment_status === 'processing')
        <form class="mt-4" method="POST" action="{{ route('admin.orders.update', $order) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="fulfillment_status" value="ready">
            <button class="rounded-lg bg-stone-900 px-4 py-2 font-medium text-white" type="submit">Tandai siap</button>
        </form>
    @elseif($order->fulfillment_status === 'ready')
        <form class="mt-4" method="POST" action="{{ route('admin.orders.update', $order) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="fulfillment_status" value="completed">
            <button class="rounded-lg bg-stone-900 px-4 py-2 font-medium text-white" type="submit">Tandai selesai</button>
        </form>
    @elseif($order->fulfillment_status === 'new' && $order->payment?->status === 'pending')
        <p class="mt-2 text-sm text-stone-600">Pesanan yang menunggu pembayaran hanya dapat dibatalkan; pembayaran terverifikasi akan diteruskan ke proses pemenuhan.</p>
        <form class="mt-4" method="POST" action="{{ route('admin.orders.update', $order) }}" onsubmit="return confirm('Batalkan pesanan ini dan lepaskan reservasi stok?')">
            @csrf
            @method('PATCH')
            <input type="hidden" name="fulfillment_status" value="cancelled">
            <button class="rounded-lg border border-red-300 px-4 py-2 font-medium text-red-700 hover:bg-red-50" type="submit">Batalkan pesanan</button>
        </form>
    @else
        <p class="mt-2 text-sm text-stone-600">Tidak ada perubahan status pemenuhan yang tersedia.</p>
    @endif
    @error('fulfillment_status')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
</section>

<section class="mt-5 rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
    <h2 class="font-semibold">Riwayat pembayaran</h2>
    <ol class="mt-4 space-y-3 text-sm">
        @forelse($order->payment?->events ?? [] as $event)
            <li class="border-l-2 border-stone-200 pl-3">
                <p><span class="capitalize">{{ $event->from_status ?? '—' }}</span> → <span class="capitalize">{{ $event->to_status }}</span></p>
                <p class="text-stone-500">{{ $event->created_at->format('d M Y H:i') }} · {{ $event->admin?->name ?? 'Sistem' }}</p>
                @if($event->transaction_reference)<p>Referensi: {{ $event->transaction_reference }}</p>@endif
                @if($event->reason)<p>Catatan: {{ $event->reason }}</p>@endif
            </li>
        @empty
            <li class="text-stone-500">Tidak ada riwayat pembayaran yang tercatat.</li>
        @endforelse
    </ol>
</section>

<a class="mt-5 inline-block text-sm font-medium text-orange-800 hover:underline" href="{{ route('admin.orders.index') }}">← Kembali ke pesanan</a>
@endsection
