<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payout Bank Accounts - Dowa Reseller</title>
    <style>
        :root { --primary: #10B981; --bg-surface: #FAFAFA; --card-bg: #FFFFFF; --text-main: #111827; --text-muted: #6B7280; --border-color: #E5E7EB; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); color: var(--text-main); padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-top: 1.5rem; }
        .btn { padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 0.85rem; border: none; cursor: pointer; display: inline-block; }
        .btn-primary { background: var(--primary); color: #FFF; }
        .btn-secondary { background: #E5E7EB; color: #111827; }
        .bank-item { border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="color: #064E3B;">Payout Bank Accounts</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Manage verified bank accounts for receiving profit withdrawals.</p>
            </div>
            <a href="{{ route('reseller.banks.create') }}" class="btn btn-primary">+ Add New Bank Account</a>
        </div>

        @if (session('success'))
            <div style="background: #D1FAE5; border: 1px solid #6EE7B7; color: #065F46; padding: 0.75rem; border-radius: 6px; margin-top: 1rem;">{{ session('success') }}</div>
        @endif

        <div class="card">
            @if ($bankAccounts->isEmpty())
                <p style="color: var(--text-muted); text-align: center; padding: 2rem;">No bank accounts added yet.</p>
            @else
                @foreach ($bankAccounts as $bank)
                    <div class="bank-item">
                        <div>
                            <div style="font-weight: 700; font-size: 1rem;">{{ $bank->bank_name }}</div>
                            <div style="font-size: 0.9rem; color: #374151;">{{ $bank->account_name }} • <span style="font-family: monospace;">{{ $bank->account_number }}</span></div>
                            <div style="font-size: 0.75rem; color: #059669; font-weight: 600; margin-top: 0.25rem;">Status: {{ ucfirst($bank->verification_status) }}</div>
                        </div>
                        <div>
                            @if ($bank->is_default)
                                <span style="background: #D1FAE5; color: #059669; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 999px; font-weight: 700;">DEFAULT</span>
                            @else
                                <form method="POST" action="{{ route('reseller.banks.default', $bank->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary">Set Default</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</body>
</html>
