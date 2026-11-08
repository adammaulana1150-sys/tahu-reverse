<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = Customer::query()
            ->withCount('orders')
            ->latest()
            ->paginate(20);

        return view('admin.customers.index', compact('customers'));
    }
}
