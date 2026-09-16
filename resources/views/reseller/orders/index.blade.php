<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Dowa Reseller</title>
    <style>
        :root { --primary: #10B981; --bg-surface: #FAFAFA; --card-bg: #FFFFFF; --text-main: #111827; --text-muted: #6B7280; --border-color: #E5E7EB; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); color: var(--text-main); padding: 2rem; }
        .container { max-width: 1100px; margin: 0 auto; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-top: 1.5rem; }
        .table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .table th, .table td { padding: 0.75rem; border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.875rem; }
        .badge { font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 999px; font-weight: 600; text-transform: uppercase; }
        .badge-paid { background: #D1FAE5; color: #059669; }
        .badge-unpaid { background: #FEE2E2; color: #991B1B; }
        .badge-processing { background: #FEF3C7; color: #D97706; }
        .btn { padding: 0.4rem 0.8rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; background: #E5E7EB; color: #111827; font-weight: 600; }
        .form-control { padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: #064E3B;">Store Orders</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Monitor customer purchases, payment verification, and order fulfillment status.</p>

        <div class="card">
            <form method="GET" action="{{ route('reseller.orders.index') }}" style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <input type="text" name="search" class="form-control" placeholder="Search order reference..." value="{{ request('search') }}">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
                <select name="payment_status" class="form-control">
                    <option value="">All Payments</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
                <button type="submit" class="btn">Filter</button>
            </form>

            <table class="table">
                <thead>
                    <tr>
                        <th>Order Ref</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Profit</th>
                        <th>Payment</th>
                        <th>Order Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td style="font-family: monospace; font-weight: 700;">{{ $order->order_number }}</td>
                            <td>{{ $order->customer ? $order->customer->first_name . ' ' . $order->customer->last_name : 'Guest' }}</td>
                            <td>{{ $order->service ? $order->service->name : 'N/A' }}</td>
                            <td>{{ number_format($order->quantity) }}</td>
                            <td style="font-weight: 700;">${{ number_format($order->customer_total_price, 2) }}</td>
                            <td style="color: #059669; font-weight: 700;">${{ number_format($order->profit_amount, 2) }}</td>
                            <td><span class="badge {{ $order->payment_status === 'paid' ? 'badge-paid' : 'badge-unpaid' }}">{{ $order->payment_status }}</span></td>
                            <td><span class="badge badge-processing">{{ $order->status }}</span></td>
                            <td><a href="{{ route('reseller.orders.show', $order->id) }}" class="btn">View Details</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 1rem;">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</body>
</html>
