<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Store Services - Dowa Reseller</title>
    <style>
        :root { --primary: #10B981; --primary-dark: #059669; --bg-surface: #FAFAFA; --card-bg: #FFFFFF; --text-main: #111827; --text-muted: #6B7280; --border-color: #E5E7EB; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); color: var(--text-main); padding: 2rem; }
        .container { max-width: 1100px; margin: 0 auto; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-top: 1.5rem; }
        .btn { padding: 0.4rem 0.8rem; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 0.85rem; border: none; cursor: pointer; display: inline-block; }
        .btn-primary { background: var(--primary); color: #FFF; }
        .btn-secondary { background: #E5E7EB; color: #111827; }
        .btn-danger { background: #FEE2E2; color: #991B1B; }
        .table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .table th, .table td { padding: 0.75rem; border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.875rem; }
        .badge { font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 999px; font-weight: 600; text-transform: uppercase; }
        .badge-active { background: #D1FAE5; color: #059669; }
        .badge-inactive { background: #FEE2E2; color: #991B1B; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: #064E3B;">My Store Services & Pricing</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Select platform services to display on your storefront and configure custom pricing margins.</p>

        @if (session('success'))
            <div style="background: #D1FAE5; border: 1px solid #6EE7B7; color: #065F46; padding: 0.75rem; border-radius: 6px; margin-top: 1rem;">{{ session('success') }}</div>
        @endif

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Platform / Service</th>
                        <th>Dowa Base Cost</th>
                        <th>My Markup</th>
                        <th>My Selling Price</th>
                        <th>Estimated Profit / 1k</th>
                        <th>Store Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($allServices as $service)
                        @php
                            $ts = $tenantServices->get($service->id);
                            $isEnabled = $ts && $ts->status === 'active';
                        @endphp
                        <tr>
                            <td style="font-weight: 600;">
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $service->platform }} • {{ $service->category }}</div>
                                {{ $service->name }}
                            </td>
                            <td>${{ number_format($service->base_price, 4) }}</td>
                            <td>{{ $ts ? $ts->markup_value . ($ts->markup_type === 'percentage' ? '%' : ' ($)') : '-' }}</td>
                            <td style="font-weight: 700; color: #059669;">{{ $ts ? '$'.number_format($ts->selling_price, 4) : '-' }}</td>
                            <td style="color: #047857; font-weight: 600;">{{ $ts ? '$'.number_format($ts->selling_price - $service->base_price, 4) : '-' }}</td>
                            <td>
                                <span class="badge {{ $isEnabled ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $isEnabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                            <td style="display: flex; gap: 0.5rem;">
                                <form method="POST" action="{{ route('reseller.services.toggle', $service->id) }}">
                                    @csrf
                                    <button type="submit" class="btn {{ $isEnabled ? 'btn-danger' : 'btn-primary' }}">
                                        {{ $isEnabled ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                @if ($ts)
                                    <a href="{{ route('reseller.services.edit', $ts->id) }}" class="btn btn-secondary">Edit Price</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
