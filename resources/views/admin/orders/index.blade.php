@extends('layouts.admin')

@section('title', 'Pesanan')
@section('heading', 'Pesanan')

@section('content')
<form class="mb-5 flex flex-col gap-3 rounded-xl border border-stone-200 bg-white p-4 sm:flex-row" method="GET">
    <input class="min-w-0 flex-1 rounded-lg border-stone-300" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, atau telepon">
    <select class="rounded-lg border-stone-300" name="fulfillment_status">
        <option value="">Semua status pesanan</option>
        @foreach(\App\Models\Order::FULFILLMENT_STATUSES as $status)<option value="{{ $status }}" @selected(request('fulfillment_status') === $status)>{{ ucfirst($status) }}</option>@endforeach
    </select>
    <select class="rounded-lg border-stone-300" name="payment_status">
        <option value="">Semua pembayaran</option>
        @foreach(\App\Models\Payment::STATUSES as $status)<option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ ucfirst($status) }}</option>@endforeach
    </select>
    <button class="rounded-lg bg-stone-900 px-4 py-2 font-medium text-white" type="submit">Filter</button>
</form>
<div class="overflow-x-auto rounded-xl border border-stone-200 bg-white shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="bg-stone-50 text-stone-600"><tr><th class="px-4 py-3">Kode</th><th class="px-4 py-3">Pelanggan</th><th class="px-4 py-3">Produk</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Pembayaran</th><th class="px-4 py-3">Pesanan</th><th class="px-4 py-3"></th></tr></thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($orders as $order)
                <tr>
                    <td class="whitespace-nowrap px-4 py-3 font-medium">{{ $order->order_code }}</td>
                    <td class="px-4 py-3">{{ $order->customer->name }}<br><span class="text-stone-500">{{ $order->customer->phone }}</span></td>
                    <td class="px-4 py-3">{{ $order->product->name }} × {{ $order->quantity }}</td>
                    <td class="whitespace-nowrap px-4 py-3">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 capitalize">{{ $order->payment?->status ?? 'legacy' }}</td>
                    <td class="px-4 py-3 capitalize">{{ $order->fulfillment_status }}</td>
                    <td class="px-4 py-3"><a class="font-medium text-orange-800 hover:underline" href="{{ route('admin.orders.show', $order) }}">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-stone-500">Pesanan tidak ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $orders->links() }}</div>
@endsection
