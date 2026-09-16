<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - {{ $store->name }}</title>
    <style>
        :root { --primary: #10B981; --primary-dark: #059669; --bg-surface: #FAFAFA; --card-bg: #FFFFFF; --text-main: #111827; --text-muted: #6B7280; --border-color: #E5E7EB; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); padding: 2rem 1rem; }
        .container { max-width: 600px; margin: 0 auto; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; }
        .form-control { width: 100%; padding: 0.65rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.95rem; }
        .btn-pay { width: 100%; background: var(--primary); color: #FFF; padding: 0.85rem; border-radius: 8px; font-weight: 700; font-size: 1rem; border: none; cursor: pointer; margin-top: 1.5rem; }
        .btn-pay:hover { background: var(--primary-dark); }
        .summary-card { background: #F3F4F6; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="color: #064E3B; margin-bottom: 0.5rem; text-align: center;">Checkout — {{ $store->name }}</h2>
        <p style="color: var(--text-muted); text-align: center; font-size: 0.9rem; margin-bottom: 2rem;">Complete customer information to proceed to secure Flutterwave payment.</p>

        @if ($errors->any())
            <div style="background: #FEE2E2; color: #991B1B; padding: 0.75rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.85rem;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="summary-card">
                <div style="font-weight: 700; color: #111827; font-size: 1rem;">{{ $service->name }}</div>
                <div style="color: #6B7280; font-size: 0.85rem; margin-top: 0.25rem;">Target: {{ $target }}</div>
                <div style="display: flex; justify-content: space-between; margin-top: 0.75rem; border-top: 1px solid #E5E7EB; padding-top: 0.75rem;">
                    <span>Quantity: <strong>{{ number_format($quantity) }}</strong></span>
                    <span style="color: #059669; font-weight: 800; font-size: 1.1rem;">Total: ${{ number_format($totalPrice, 4) }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('storefront.checkout.process', $username) }}">
                @csrf
                <input type="hidden" name="tenant_service_id" value="{{ $tenantService->id }}">
                <input type="hidden" name="quantity" value="{{ $quantity }}">
                <input type="hidden" name="target" value="{{ $target }}">

                <div style="display: flex; gap: 1rem;">
                    <div class="form-group" style="flex: 1;">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label>Phone Number (Optional)</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>

                <button type="submit" class="btn-pay">💳 Pay ${{ number_format($totalPrice, 2) }} via Flutterwave &rarr;</button>
            </form>
        </div>
    </div>
</body>
</html>
