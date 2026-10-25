@extends('layouts.admin')

@section('title', 'Pelanggan')
@section('heading', 'Pelanggan')

@section('content')
<div class="overflow-x-auto rounded-xl border border-stone-200 bg-white shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="bg-stone-50 text-stone-600"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Telepon</th><th class="px-4 py-3">Jumlah pesanan</th><th class="px-4 py-3">Total dibelanjakan</th></tr></thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($customers as $customer)
                <tr><td class="px-4 py-3 font-medium">{{ $customer->name }}</td><td class="px-4 py-3">{{ $customer->phone }}</td><td class="px-4 py-3">{{ $customer->orders_count }}</td><td class="px-4 py-3">Rp {{ number_format($customer->total_spent ?? 0, 0, ',', '.') }}</td></tr>
            @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-stone-500">Belum ada pelanggan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $customers->links() }}</div>
@endsection
