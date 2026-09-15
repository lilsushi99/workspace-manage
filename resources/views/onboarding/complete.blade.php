<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Ready! - Dowa</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background-color: #FAFAFA; color: #111827; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .success-card { background: #FFF; border: 1px solid #E5E7EB; border-radius: 12px; max-width: 550px; width: 100%; padding: 2.5rem; text-align: center; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .icon-circle { width: 70px; height: 70px; background: #D1FAE5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1.5rem; }
        h1 { color: #064E3B; font-size: 1.75rem; margin-bottom: 0.5rem; }
        p { color: #6B7280; font-size: 0.95rem; margin-bottom: 2rem; line-height: 1.5; }
        .store-url-box { background: #F3F4F6; border: 1px dashed #D1D5DB; padding: 1rem; border-radius: 8px; font-family: monospace; font-size: 0.95rem; color: #047857; margin-bottom: 2rem; word-break: break-all; }
        .btn-group { display: flex; gap: 1rem; justify-content: center; }
        .btn { padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 0.95rem; display: inline-block; }
        .btn-primary { background: #10B981; color: #FFF; }
        .btn-primary:hover { background: #059669; }
        .btn-secondary { background: #E5E7EB; color: #111827; }
        .btn-secondary:hover { background: #D1D5DB; }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="icon-circle">✓</div>
        <h1>Your Dowa Store is Ready!</h1>
        <p>Congratulations! Your SMM reseller storefront has been initialized with database-backed services, custom pricing, and profit ledger.</p>

        @if ($store)
            <div class="store-url-box">
                {{ url('/store/'.$store->slug) }}
            </div>
        @endif

        <div class="btn-group">
            <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to Reseller Dashboard &rarr;</a>
            @if ($store)
                <a href="{{ url('/store/'.$store->slug) }}" target="_blank" class="btn btn-secondary">Visit My Store &nearr;</a>
            @endif
        </div>
    </div>
</body>
</html>
