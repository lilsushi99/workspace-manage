<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Provider - Dowa Admin</title>
    <style>
        :root { --primary: #10B981; --primary-dark: #059669; --bg-surface: #FAFAFA; --card-bg: #FFFFFF; --text-main: #111827; --text-muted: #6B7280; --border-color: #E5E7EB; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); color: var(--text-main); padding: 2rem; }
        .container { max-width: 600px; margin: 0 auto; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 2rem; margin-top: 1.5rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-weight: 500; font-size: 0.875rem; margin-bottom: 0.35rem; }
        .form-control { width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.95rem; }
        .btn { padding: 0.65rem 1.5rem; border-radius: 6px; font-weight: 600; text-decoration: none; font-size: 0.95rem; border: none; cursor: pointer; }
        .btn-primary { background: var(--primary); color: #FFF; }
        .btn-secondary { background: #E5E7EB; color: #111827; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: #064E3B;">Edit SMM Provider</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Update endpoint configuration and API authentication keys.</p>

        <div class="card">
            <form method="POST" action="{{ route('admin.providers.update', $provider->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Provider Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $provider->name) }}" required>
                </div>

                <div class="form-group">
                    <label>API Endpoint URL</label>
                    <input type="url" name="api_url" class="form-control" value="{{ old('api_url', $provider->api_url) }}" required>
                </div>

                <div class="form-group">
                    <label>API Key (Leave blank to keep existing encrypted key)</label>
                    <input type="password" name="api_key" class="form-control" placeholder="••••••••••••••••">
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="active" {{ $provider->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $provider->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="disabled" {{ $provider->status === 'disabled' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 2rem;">
                    <a href="{{ route('admin.providers.index') }}" class="btn btn-secondary">&larr; Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Provider Changes &rarr;</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
