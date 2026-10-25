<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = Customer::query()
            ->withCount('orders')
            ->withSum([
                'orders as total_spent' => fn (Builder $query) => $query->whereHas(
                    'payment',
                    fn (Builder $payment) => $payment->where('status', 'paid')
                ),
            ], 'total')
            ->latest()
            ->paginate(20);

        return view('admin.customers.index', compact('customers'));
    }
}
