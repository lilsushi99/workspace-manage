<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Inspection - Order {{ $order->order_number }}</title>
    <style>
        :root { --primary: #10B981; --bg-surface: #FAFAFA; --card-bg: #FFFFFF; --border-color: #E5E7EB; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); padding: 2rem; }
        .container { max-width: 850px; margin: 0 auto; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-top: 1rem; }
        .btn { padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; background: #E5E7EB; color: #111827; font-weight: 600; display: inline-block; border: none; cursor: pointer; }
        .btn-primary { background: var(--primary); color: #FFF; }
        .timeline { border-left: 2px solid var(--primary); padding-left: 1rem; margin-top: 1rem; }
        .timeline-item { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('admin.orders.index') }}" class="btn">&larr; Back to Admin Orders</a>

        @if (session('success'))
            <div style="background: #D1FAE5; border: 1px solid #6EE7B7; color: #065F46; padding: 0.75rem; border-radius: 6px; margin-top: 1rem;">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="color: #064E3B;">Admin Inspection — Order #{{ $order->order_number }}</h2>
                    <p style="color: #6B7280; font-size: 0.85rem;">Tenant: {{ $order->tenant ? $order->tenant->name : 'N/A' }}</p>
                </div>
                @if ($order->payment_status === 'paid' && empty($order->provider_order_id))
                    <form method="POST" action="{{ route('admin.orders.retry', $order->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">⚡ Retry Provider Fulfilment</button>
                    </form>
                @endif
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                <div>
                    <h4 style="color: #374151; margin-bottom: 0.5rem;">Customer Details</h4>
                    <p style="font-size: 0.9rem;"><strong>Name:</strong> {{ $order->customer ? $order->customer->first_name . ' ' . $order->customer->last_name : 'Guest' }}</p>
                    <p style="font-size: 0.9rem;"><strong>Email:</strong> {{ $order->customer ? $order->customer->email : 'N/A' }}</p>
                </div>
                <div>
                    <h4 style="color: #374151; margin-bottom: 0.5rem;">Financial Breakdown</h4>
                    <p style="font-size: 0.9rem;"><strong>Customer Price:</strong> ${{ number_format($order->customer_total_price, 4) }}</p>
                    <p style="font-size: 0.9rem;"><strong>Provider Cost:</strong> ${{ number_format($order->provider_total_cost, 4) }}</p>
                    <p style="font-size: 0.9rem; color: #059669;"><strong>Reseller Profit:</strong> ${{ number_format($order->profit_amount, 4) }}</p>
                </div>
            </div>

            <div style="margin-top: 1.5rem;">
                <h4 style="color: #374151; margin-bottom: 0.5rem;">Provider & Target Info</h4>
                <p style="font-size: 0.9rem;"><strong>Target URL:</strong> <a href="{{ $order->target }}" target="_blank">{{ $order->target }}</a></p>
                <p style="font-size: 0.9rem;"><strong>Provider:</strong> {{ $order->provider ? $order->provider->name : 'N/A' }}</p>
                <p style="font-size: 0.9rem;"><strong>Provider Order ID:</strong> {{ $order->provider_order_id ?: 'Not submitted yet' }}</p>
            </div>

            <div style="margin-top: 1.5rem;">
                <h4 style="color: #374151; margin-bottom: 0.5rem;">Order History Timeline</h4>
                <div class="timeline">
                    @foreach ($order->statusHistories as $history)
                        <div class="timeline-item">
                            <div style="font-weight: 600; font-size: 0.85rem; color: #059669;">
                                {{ ucfirst($history->old_status) }} &rarr; {{ ucfirst($history->new_status) }} ({{ $history->source }})
                            </div>
                            <div style="font-size: 0.8rem; color: #6B7280;">
                                {{ $history->message }} • {{ $history->created_at ? $history->created_at->format('M d H:i:s') : '' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</body>
</html>
