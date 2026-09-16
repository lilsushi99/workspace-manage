<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - {{ $order->order_number }}</title>
    <style>
        :root { --primary: #10B981; --bg-surface: #FAFAFA; --card-bg: #FFFFFF; --border-color: #E5E7EB; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-top: 1rem; }
        .btn { padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; background: #E5E7EB; color: #111827; font-weight: 600; display: inline-block; }
        .timeline { border-left: 2px solid var(--primary); padding-left: 1rem; margin-top: 1rem; }
        .timeline-item { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('reseller.orders.index') }}" class="btn">&larr; Back to Orders</a>

        <div class="card">
            <h2 style="color: #064E3B;">Order #{{ $order->order_number }}</h2>
            <p style="color: #6B7280; font-size: 0.85rem;">Created {{ $order->created_at ? $order->created_at->format('M d, Y H:i A') : 'N/A' }}</p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                <div>
                    <h4 style="color: #374151; margin-bottom: 0.5rem;">Customer Details</h4>
                    <p style="font-size: 0.9rem;"><strong>Name:</strong> {{ $order->customer ? $order->customer->first_name . ' ' . $order->customer->last_name : 'Guest' }}</p>
                    <p style="font-size: 0.9rem;"><strong>Email:</strong> {{ $order->customer ? $order->customer->email : 'N/A' }}</p>
                </div>
                <div>
                    <h4 style="color: #374151; margin-bottom: 0.5rem;">Order & Financials</h4>
                    <p style="font-size: 0.9rem;"><strong>Retail Price:</strong> ${{ number_format($order->customer_total_price, 4) }}</p>
                    <p style="font-size: 0.9rem;"><strong>Base Cost:</strong> ${{ number_format($order->provider_total_cost, 4) }}</p>
                    <p style="font-size: 0.9rem; color: #059669;"><strong>Net Profit:</strong> ${{ number_format($order->profit_amount, 4) }}</p>
                </div>
            </div>

            <div style="margin-top: 1.5rem;">
                <h4 style="color: #374151; margin-bottom: 0.5rem;">Target & Fulfilment</h4>
                <p style="font-size: 0.9rem;"><strong>Target URL:</strong> <a href="{{ $order->target }}" target="_blank">{{ $order->target }}</a></p>
                <p style="font-size: 0.9rem;"><strong>Provider Ref:</strong> {{ $order->provider_order_id ?: 'Awaiting Provider Submission' }}</p>
                <p style="font-size: 0.9rem;"><strong>Status:</strong> {{ ucfirst($order->status) }} (Payment: {{ ucfirst($order->payment_status) }})</p>
            </div>

            <div style="margin-top: 1.5rem;">
                <h4 style="color: #374151; margin-bottom: 0.5rem;">Audit & Status Timeline</h4>
                <div class="timeline">
                    @foreach ($order->statusHistories as $history)
                        <div class="timeline-item">
                            <div style="font-weight: 600; font-size: 0.85rem; color: #059669;">
                                {{ ucfirst($history->old_status) }} &rarr; {{ ucfirst($history->new_status) }}
                            </div>
                            <div style="font-size: 0.8rem; color: #6B7280;">
                                {{ $history->message }} • {{ $history->created_at ? $history->created_at->format('M d H:i') : '' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</body>
</html>
