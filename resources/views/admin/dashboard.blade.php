@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @foreach([
        ['Total pelanggan', $totalCustomers],
        ['Pembeli', $totalBuyers],
        ['Total pesanan', $totalOrders],
        ['Pesanan dibayar', $paidOrders],
        ['Menunggu pembayaran', $pendingOrders],
        ['Pesanan dibatalkan', $cancelledOrders],
        ['Pendapatan', 'Rp '.number_format($revenue, 0, ',', '.')],
    ] as [$label, $value])
        <section class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-bold">{{ $value }}</p>
        </section>
    @endforeach
</div>
<section class="mt-8 overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
    <div class="border-b border-stone-200 px-5 py-4"><h2 class="font-semibold">5 pesanan terbaru</h2></div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-stone-50 text-stone-600"><tr><th class="px-5 py-3">Kode</th><th class="px-5 py-3">Pelanggan</th><th class="px-5 py-3">Total</th><th class="px-5 py-3">Pembayaran</th><th class="px-5 py-3">Pesanan</th></tr></thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($latestOrders as $order)
                    <tr>
                        <td class="px-5 py-3"><a class="font-medium text-orange-800 hover:underline" href="{{ route('admin.orders.show', $order) }}">{{ $order->order_code }}</a></td>
                        <td class="px-5 py-3">{{ $order->customer->name }}</td>
                        <td class="px-5 py-3">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 capitalize">{{ $order->payment?->status ?? 'legacy' }}</td>
                        <td class="px-5 py-3 capitalize">{{ $order->fulfillment_status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-stone-500">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
