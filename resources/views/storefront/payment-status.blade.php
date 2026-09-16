<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - {{ $store->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: #FAFAFA; color: #111827; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .status-card { background: #FFF; border: 1px solid #E5E7EB; border-radius: 12px; max-width: 550px; width: 100%; padding: 2.5rem; text-align: center; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1.5rem; font-weight: 800; }
        .icon-success { background: #D1FAE5; color: #059669; }
        .icon-pending { background: #FEF3C7; color: #D97706; }
        .icon-failed { background: #FEE2E2; color: #DC2626; }
        h1 { color: #064E3B; font-size: 1.5rem; margin-bottom: 0.5rem; }
        p { color: #6B7280; font-size: 0.95rem; margin-bottom: 1.5rem; }
        .info-box { background: #F3F4F6; padding: 1rem; border-radius: 8px; text-align: left; font-size: 0.875rem; margin-bottom: 2rem; }
        .btn { padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 0.95rem; display: inline-block; background: #10B981; color: #FFF; }
        .btn:hover { background: #059669; }
    </style>
</head>
<body>
    <div class="status-card">
        @if ($order && $order->payment_status === 'paid')
            <div class="icon icon-success">✓</div>
            <h1>Payment Confirmed!</h1>
            <p>Thank you! Your payment has been verified by the server and your order is active.</p>
        @elseif ($order && $order->payment_status === 'pending')
            <div class="icon icon-pending">⏳</div>
            <h1>Verification Pending</h1>
            <p>We are verifying your transaction with Flutterwave. Please refresh in a moment.</p>
        @else
            <div class="icon icon-failed">✕</div>
            <h1>Payment Verification Failed</h1>
            <p>We could not confirm payment verification. Please contact support if you were charged.</p>
        @endif

        @if ($order)
            <div class="info-box">
                <div style="margin-bottom: 0.35rem;"><strong>Order Reference:</strong> {{ $order->order_number }}</div>
                <div style="margin-bottom: 0.35rem;"><strong>Service:</strong> {{ $order->service ? $order->service->name : 'Growth Service' }}</div>
                <div style="margin-bottom: 0.35rem;"><strong>Quantity:</strong> {{ number_format($order->quantity) }}</div>
                <div><strong>Amount Paid:</strong> ${{ number_format($order->customer_total_price, 2) }}</div>
            </div>
        @endif

        <a href="{{ route('storefront.show', $username) }}" class="btn">Return to Storefront &rarr;</a>
    </div>
</body>
</html>
