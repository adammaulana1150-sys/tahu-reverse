<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $product = Product::query()->firstOrFail();

        return view('admin.dashboard', [
            'totalCustomers' => Customer::count(),
            'totalOrders' => Order::count(),
            'cancelledOrders' => Order::where('fulfillment_status', 'cancelled')->count(),
            'productStock' => (int) $product->stock,
            'latestOrders' => Order::with(['customer', 'product'])->latest()->limit(5)->get(),
        ]);
    }
}
