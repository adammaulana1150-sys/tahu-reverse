<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'fulfillment_status' => ['nullable', 'in:'.implode(',', Order::FULFILLMENT_STATUSES)],
        ]);

        $orders = Order::query()
            ->with(['customer', 'product'])
            ->when($validated['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('order_code', 'like', "%{$search}%")
                        ->orWhereHas('customer', function (Builder $customer) use ($search): void {
                            $customer->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($validated['fulfillment_status'] ?? null, fn (Builder $query, string $status) => $query->where('fulfillment_status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'product']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'fulfillment_status' => ['required', 'in:'.implode(',', Order::FULFILLMENT_STATUSES)],
        ]);

        $allowed = match ($order->fulfillment_status) {
            'new' => ['cancelled'],
            'processing' => ['ready'],
            'ready' => ['completed'],
            default => [],
        };

        if (! in_array($validated['fulfillment_status'], $allowed, true)) {
            return back()->withErrors([
                'fulfillment_status' => 'Perubahan status pesanan ini tidak diizinkan.',
            ]);
        }

        $order->update(['fulfillment_status' => $validated['fulfillment_status']]);

        return back()->with('status', 'Status pemenuhan pesanan berhasil diperbarui.');
    }
}
