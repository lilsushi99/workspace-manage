<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service Price - Dowa Reseller</title>
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
        <h1 style="color: #064E3B;">Configure Service Price</h1>

        <div class="card">
            <h3 style="margin-bottom: 0.25rem;">{{ $tenantService->service->name }}</h3>
            <p style="color: #6B7280; font-size: 0.85rem; margin-bottom: 1.5rem;">Base Dowa Cost: <strong>${{ number_format($tenantService->service->base_price, 4) }}</strong> / 1k</p>

            <form method="POST" action="{{ route('reseller.services.update', $tenantService->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Markup Method</label>
                    <select name="markup_type" class="form-control">
                        <option value="percentage" {{ $tenantService->markup_type === 'percentage' ? 'selected' : '' }}>Percentage Markup (%)</option>
                        <option value="fixed_markup" {{ $tenantService->markup_type === 'fixed_markup' ? 'selected' : '' }}>Fixed Markup ($)</option>
                        <option value="fixed_price" {{ $tenantService->markup_type === 'fixed_price' ? 'selected' : '' }}>Custom Retail Price ($)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Markup Value or Price</label>
                    <input type="number" step="0.0001" name="markup_value" class="form-control" value="{{ old('markup_value', $tenantService->markup_value) }}" required>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 2rem;">
                    <a href="{{ route('reseller.services.index') }}" class="btn btn-secondary">&larr; Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Price &rarr;</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
