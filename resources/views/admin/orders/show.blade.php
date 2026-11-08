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
            <div class="flex justify-between gap-4"><dt class="text-stone-500">Status pemenuhan</dt><dd class="capitalize">{{ $order->fulfillment_status }}</dd></div>
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
    @elseif($order->fulfillment_status === 'new')
        <form class="mt-4" method="POST" action="{{ route('admin.orders.update', $order) }}" onsubmit="return confirm('Batalkan pesanan ini?')">
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


<a class="mt-5 inline-block text-sm font-medium text-orange-800 hover:underline" href="{{ route('admin.orders.index') }}">← Kembali ke pesanan</a>
@endsection
