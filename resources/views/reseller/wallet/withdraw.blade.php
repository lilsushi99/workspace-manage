<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Withdrawal - Dowa Reseller</title>
    <style>
        :root { --primary: #10B981; --bg-surface: #FAFAFA; --card-bg: #FFFFFF; --border-color: #E5E7EB; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); padding: 2rem; }
        .container { max-width: 550px; margin: 0 auto; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 2rem; margin-top: 1rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-weight: 500; font-size: 0.875rem; margin-bottom: 0.35rem; }
        .form-control { width: 100%; padding: 0.65rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.95rem; }
        .btn { padding: 0.65rem 1.5rem; border-radius: 6px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; text-decoration: none; }
        .btn-primary { background: var(--primary); color: #FFF; }
        .btn-secondary { background: #E5E7EB; color: #111827; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: #064E3B;">Request Profit Withdrawal</h1>

        @if ($errors->any())
            <div style="background: #FEE2E2; color: #991B1B; padding: 0.75rem; border-radius: 6px; margin-top: 1rem;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div style="background: #D1FAE5; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; justify-content: space-between;">
                <span>Available Balance:</span>
                <strong style="color: #059669; font-size: 1.1rem;">${{ number_format($wallet->available_balance, 2) }}</strong>
            </div>

            <form method="POST" action="{{ route('reseller.wallet.withdraw.store') }}">
                @csrf

                <div class="form-group">
                    <label>Withdrawal Amount ($)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="Minimum $5.00" value="{{ old('amount') }}" max="{{ $wallet->available_balance }}" required>
                </div>

                <div class="form-group">
                    <label>Destination Bank Account</label>
                    <select name="bank_account_id" class="form-control" required>
                        @foreach ($bankAccounts as $bank)
                            <option value="{{ $bank->id }}" {{ $defaultBank && $defaultBank->id === $bank->id ? 'selected' : '' }}>
                                {{ $bank->bank_name }} — {{ $bank->account_name }} ({{ $bank->account_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 2rem;">
                    <a href="{{ route('reseller.wallet.index') }}" class="btn btn-secondary">&larr; Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Withdrawal Request &rarr;</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
