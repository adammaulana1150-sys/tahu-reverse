<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\ManualPaymentVerificationService;
use App\Services\OrderFulfillmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'fulfillment_status' => ['nullable', 'in:'.implode(',', Order::FULFILLMENT_STATUSES)],
            'payment_status' => ['nullable', 'in:'.implode(',', Payment::STATUSES)],
        ]);

        $orders = Order::query()
            ->with(['customer', 'product', 'payment'])
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
            ->when($validated['payment_status'] ?? null, fn (Builder $query, string $status) => $query->whereHas('payment', fn (Builder $payment) => $payment->where('status', $status)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'product', 'payment.events.admin']);

        $qrisImagePath = Setting::query()->whereKey('qris_image_path')->value('value');
        $qrisImageUrl = $qrisImagePath && Storage::disk('public')->exists($qrisImagePath)
            ? Storage::disk('public')->url($qrisImagePath)
            : null;

        return view('admin.orders.show', compact('order', 'qrisImageUrl'));
    }

    public function update(Request $request, Order $order, OrderFulfillmentService $fulfillment): RedirectResponse
    {
        $validated = $request->validate([
            'fulfillment_status' => ['required', 'in:'.implode(',', Order::FULFILLMENT_STATUSES)],
        ]);
        $admin = $request->user();
        abort_unless($admin instanceof User, 403);

        try {
            $fulfillment->update($order, $admin, $validated['fulfillment_status']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Status pemenuhan pesanan berhasil diperbarui.');
    }

    public function verifyPayment(Request $request, Order $order, ManualPaymentVerificationService $payments): RedirectResponse
    {
        $validated = $request->validate([
            'verification_reference' => ['required', 'string', 'max:120'],
            'confirm' => ['required', 'accepted'],
        ]);
        $admin = $request->user();
        abort_unless($admin instanceof User, 403);

        try {
            $payments->verify($order, $admin, $validated['verification_reference']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Pembayaran diverifikasi dan tercatat.');
    }

    public function rejectPayment(Request $request, Order $order, ManualPaymentVerificationService $payments): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
            'confirm' => ['required', 'accepted'],
        ]);
        $admin = $request->user();
        abort_unless($admin instanceof User, 403);

        try {
            $payments->reject($order, $admin, $validated['reason']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Pembayaran ditolak dan dicatat.');
    }
}
