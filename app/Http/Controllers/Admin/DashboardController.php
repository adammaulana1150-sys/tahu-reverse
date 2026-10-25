<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalCustomers' => Customer::count(),
            'totalBuyers' => Customer::whereHas('orders.payment', fn ($query) => $query->where('status', 'paid'))->count(),
            'totalOrders' => Order::count(),
            'paidOrders' => Payment::where('status', 'paid')->count(),
            'pendingOrders' => Payment::where('status', 'pending')->count(),
            'cancelledOrders' => Order::where('fulfillment_status', 'cancelled')->count(),
            'revenue' => Payment::where('status', 'paid')->sum('amount'),
            'latestOrders' => Order::with(['customer', 'product', 'payment'])->latest()->limit(5)->get(),
        ]);
    }
}
