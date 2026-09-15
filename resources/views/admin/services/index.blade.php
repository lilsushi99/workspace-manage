<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMM Platform Services - Dowa Admin</title>
    <style>
        :root { --primary: #10B981; --primary-dark: #059669; --bg-surface: #FAFAFA; --card-bg: #FFFFFF; --text-main: #111827; --text-muted: #6B7280; --border-color: #E5E7EB; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); color: var(--text-main); padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-top: 1.5rem; }
        .btn { padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 0.85rem; border: none; cursor: pointer; display: inline-block; }
        .btn-primary { background: var(--primary); color: #FFF; }
        .btn-secondary { background: #E5E7EB; color: #111827; }
        .table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .table th, .table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color); text-align: left; font-size: 0.875rem; }
        .badge { font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 999px; font-weight: 600; text-transform: uppercase; }
        .badge-active { background: #D1FAE5; color: #059669; }
        .badge-inactive { background: #FEE2E2; color: #991B1B; }
        .form-control { padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="color: #064E3B;">Platform Service Catalogue</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Manage internal Dowa SMM services and provider mappings.</p>
            </div>
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary">+ Add New Service</a>
        </div>

        @if (session('success'))
            <div style="background: #D1FAE5; border: 1px solid #6EE7B7; color: #065F46; padding: 0.75rem; border-radius: 6px; margin-top: 1rem;">{{ session('success') }}</div>
        @endif

        <div class="card">
            <form method="GET" action="{{ route('admin.services.index') }}" style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <input type="text" name="search" class="form-control" placeholder="Search service name..." value="{{ request('search') }}">
                <select name="platform" class="form-control">
                    <option value="">All Platforms</option>
                    @foreach ($platforms as $plat)
                        <option value="{{ $plat }}" {{ request('platform') === $plat ? 'selected' : '' }}>{{ $plat }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>

            <table class="table">
                <thead>
                    <tr>
                        <th>Service Name</th>
                        <th>Platform / Category</th>
                        <th>Base Cost</th>
                        <th>Dowa Selling Price</th>
                        <th>Provider Reference</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($services as $service)
                        <tr>
                            <td style="font-weight: 600;">{{ $service->name }}</td>
                            <td>{{ $service->platform }} / {{ $service->category }}</td>
                            <td>${{ number_format($service->base_price, 4) }}</td>
                            <td style="color: #059669; font-weight: 700;">${{ number_format($service->selling_price, 4) }}</td>
                            <td>{{ $service->providerService ? $service->providerService->name : 'Manual' }}</td>
                            <td><span class="badge {{ $service->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $service->status }}</span></td>
                            <td><a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-secondary">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 1rem;">
                {{ $services->links() }}
            </div>
        </div>
    </div>
</body>
</html>
