<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SubmitOrderToProviderJob;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('access-admin');

        $query = Order::with(['tenant', 'customer', 'service', 'provider']);

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

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        Gate::authorize('access-admin');

        $order->load(['tenant', 'customer', 'service', 'store', 'provider', 'statusHistories', 'payments']);

        return view('admin.orders.show', compact('order'));
    }

    public function retry(Order $order)
    {
        Gate::authorize('access-admin');

        if ($order->payment_status !== 'paid') {
            return back()->withErrors(['order' => 'Cannot retry submission for unpaid orders.']);
        }

        if (!empty($order->provider_order_id)) {
            return back()->withErrors(['order' => 'Order has already been submitted to provider.']);
        }

        SubmitOrderToProviderJob::dispatch($order);

        return back()->with('success', "Order #{$order->order_number} queued for manual provider fulfilment retry.");
    }
}
