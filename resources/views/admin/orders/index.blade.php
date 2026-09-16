<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Platform Orders - Dowa Admin</title>
    <style>
        :root { --primary: #10B981; --bg-surface: #FAFAFA; --card-bg: #FFFFFF; --text-main: #111827; --text-muted: #6B7280; --border-color: #E5E7EB; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); color: var(--text-main); padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-top: 1.5rem; }
        .table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .table th, .table td { padding: 0.75rem; border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.875rem; }
        .badge { font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 999px; font-weight: 600; text-transform: uppercase; }
        .badge-paid { background: #D1FAE5; color: #059669; }
        .badge-unpaid { background: #FEE2E2; color: #991B1B; }
        .btn { padding: 0.4rem 0.8rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; background: #E5E7EB; color: #111827; font-weight: 600; }
        .form-control { padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: #064E3B;">Cross-Tenant Platform Orders</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Platform-wide order inspection and manual provider fulfilment retries.</p>

        @if (session('success'))
            <div style="background: #D1FAE5; border: 1px solid #6EE7B7; color: #065F46; padding: 0.75rem; border-radius: 6px; margin-top: 1rem;">{{ session('success') }}</div>
        @endif

        <div class="card">
            <form method="GET" action="{{ route('admin.orders.index') }}" style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <input type="text" name="search" class="form-control" placeholder="Search order reference..." value="{{ request('search') }}">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                </select>
                <select name="payment_status" class="form-control">
                    <option value="">All Payments</option>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                </select>
                <button type="submit" class="btn">Filter</button>
            </form>

            <table class="table">
                <thead>
                    <tr>
                        <th>Order Ref</th>
                        <th>Reseller Tenant</th>
                        <th>Service</th>
                        <th>Qty</th>
                        <th>Customer Price</th>
                        <th>Provider Cost</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td style="font-family: monospace; font-weight: 700;">{{ $order->order_number }}</td>
                            <td>{{ $order->tenant ? $order->tenant->name : 'N/A' }}</td>
                            <td>{{ $order->service ? $order->service->name : 'N/A' }}</td>
                            <td>{{ number_format($order->quantity) }}</td>
                            <td style="font-weight: 700;">${{ number_format($order->customer_total_price, 2) }}</td>
                            <td style="color: #4B5563;">${{ number_format($order->provider_total_cost, 2) }}</td>
                            <td><span class="badge {{ $order->payment_status === 'paid' ? 'badge-paid' : 'badge-unpaid' }}">{{ $order->payment_status }}</span></td>
                            <td>{{ ucfirst($order->status) }}</td>
                            <td><a href="{{ route('admin.orders.show', $order->id) }}" class="btn">Inspect</a></td>
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
