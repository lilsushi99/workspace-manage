<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;
        if (!$tenant) {
            return redirect()->route('onboarding.index');
        }

        $query = Order::where('tenant_id', $tenant->id)->with(['customer', 'service', 'store']);

        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->orderBy('id', 'desc')->paginate(15);

        return view('reseller.orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        Gate::authorize('view', $order);

        $order->load(['customer', 'service', 'store', 'provider', 'statusHistories', 'payments']);

        return view('reseller.orders.show', compact('order'));
    }
}
