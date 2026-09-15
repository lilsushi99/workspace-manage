<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMM Providers - Dowa Admin</title>
    <style>
        :root { --primary: #10B981; --primary-dark: #059669; --bg-surface: #FAFAFA; --card-bg: #FFFFFF; --text-main: #111827; --text-muted: #6B7280; --border-color: #E5E7EB; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); color: var(--text-main); padding: 2rem; }
        .container { max-width: 1100px; margin: 0 auto; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-top: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .btn { padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 0.85rem; border: none; cursor: pointer; display: inline-block; }
        .btn-primary { background: var(--primary); color: #FFF; }
        .btn-secondary { background: #E5E7EB; color: #111827; }
        .table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .table th, .table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.9rem; }
        .badge { font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 999px; font-weight: 600; text-transform: uppercase; }
        .badge-active { background: #D1FAE5; color: #059669; }
        .badge-inactive { background: #FEE2E2; color: #991B1B; }
        .alert-success { background: #D1FAE5; border: 1px solid #6EE7B7; color: #065F46; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert-error { background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: #064E3B;">SMM Provider Management</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Configure external SMM API connections and synchronize provider service catalogs.</p>

        @if (session('success'))
            <div class="alert-success" style="margin-top: 1rem;">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error" style="margin-top: 1rem;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Provider Name</th>
                        <th>API URL</th>
                        <th>Status</th>
                        <th>Synced Services</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($providers as $provider)
                        <tr>
                            <td style="font-weight: 600;">{{ $provider->name }}</td>
                            <td style="font-family: monospace; color: var(--text-muted); font-size: 0.85rem;">{{ $provider->api_url }}</td>
                            <td>
                                <span class="badge {{ $provider->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $provider->status }}
                                </span>
                            </td>
                            <td>{{ number_format($provider->provider_services_count) }} services</td>
                            <td style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('admin.providers.edit', $provider->id) }}" class="btn btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.providers.test', $provider->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary">Test Connection</button>
                                </form>
                                <form method="POST" action="{{ route('admin.providers.sync', $provider->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Sync Services</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
