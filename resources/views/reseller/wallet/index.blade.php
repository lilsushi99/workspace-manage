<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet Ledger - Dowa Reseller</title>
    <style>
        :root { --primary: #10B981; --bg-surface: #FAFAFA; --card-bg: #FFFFFF; --text-main: #111827; --text-muted: #6B7280; --border-color: #E5E7EB; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); color: var(--text-main); padding: 2rem; }
        .container { max-width: 1050px; margin: 0 auto; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 1.5rem; }
        .stat-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-top: 1.5rem; }
        .btn { padding: 0.6rem 1.2rem; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 0.9rem; border: none; cursor: pointer; display: inline-block; }
        .btn-primary { background: var(--primary); color: #FFF; }
        .table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .table th, .table td { padding: 0.75rem; border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="color: #064E3B;">Wallet Ledger & Earnings</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Track order profit earnings, ledger transactions, and request bank payouts.</p>
            </div>
            <a href="{{ route('reseller.wallet.withdraw') }}" class="btn btn-primary">💸 Request Withdrawal</a>
        </div>

        @if (session('success'))
            <div style="background: #D1FAE5; border: 1px solid #6EE7B7; color: #065F46; padding: 0.75rem; border-radius: 6px; margin-top: 1rem;">{{ session('success') }}</div>
        @endif

        <div class="stats-grid">
            <div class="stat-card">
                <div style="font-size: 0.85rem; color: var(--text-muted);">Available Balance</div>
                <div style="font-size: 2rem; font-weight: 800; color: #059669; margin-top: 0.25rem;">${{ number_format($wallet->available_balance, 2) }}</div>
            </div>
            <div class="stat-card">
                <div style="font-size: 0.85rem; color: var(--text-muted);">Pending / Reserved</div>
                <div style="font-size: 2rem; font-weight: 800; color: #D97706; margin-top: 0.25rem;">${{ number_format($wallet->pending_balance, 2) }}</div>
            </div>
            <div class="stat-card">
                <div style="font-size: 0.85rem; color: var(--text-muted);">Total Ledger Balance</div>
                <div style="font-size: 2rem; font-weight: 800; color: #111827; margin-top: 0.25rem;">${{ number_format($wallet->balance, 2) }}</div>
            </div>
        </div>

        <div class="card">
            <h3 style="color: #064E3B;">Ledger Transaction History</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Txn Ref</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Direction</th>
                        <th>Balance After</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $txn)
                        <tr>
                            <td style="font-family: monospace; font-weight: 600;">{{ $txn->reference }}</td>
                            <td><span style="font-weight: 600;">{{ ucfirst($txn->type) }}</span></td>
                            <td style="font-weight: 700; color: {{ $txn->direction === 'credit' ? '#059669' : '#DC2626' }};">
                                {{ $txn->direction === 'credit' ? '+' : '-' }}${{ number_format($txn->amount, 2) }}
                            </td>
                            <td>{{ ucfirst($txn->direction) }}</td>
                            <td>${{ number_format($txn->balance_after, 2) }}</td>
                            <td style="color: var(--text-muted); font-size: 0.85rem;">{{ $txn->description }}</td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);">{{ $txn->created_at ? $txn->created_at->format('M d, H:i') : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 1rem;">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</body>
</html>
