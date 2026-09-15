<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service - Dowa Admin</title>
    <style>
        :root { --primary: #10B981; --bg-surface: #FAFAFA; --card-bg: #FFFFFF; --border-color: #E5E7EB; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); padding: 2rem; }
        .container { max-width: 650px; margin: 0 auto; }
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
        <h1 style="color: #064E3B;">Edit Platform Service</h1>

        <div class="card">
            <form method="POST" action="{{ route('admin.services.update', $service->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Service Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $service->name) }}" required>
                </div>

                <div class="form-group">
                    <label>Platform</label>
                    <input type="text" name="platform" class="form-control" value="{{ old('platform', $service->platform) }}" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category', $service->category) }}" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $service->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label>Provider Service Mapping</label>
                    <select name="provider_service_id" class="form-control">
                        <option value="">No Provider Mapping (Manual)</option>
                        @foreach ($providerServices as $ps)
                            <option value="{{ $ps->id }}" {{ $service->provider_service_id == $ps->id ? 'selected' : '' }}>
                                {{ $ps->provider->name }} — {{ $ps->name }} (${{ $ps->provider_price }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <div class="form-group" style="flex: 1;">
                        <label>Base Cost ($ / 1k)</label>
                        <input type="number" step="0.0001" name="base_price" class="form-control" value="{{ old('base_price', $service->base_price) }}" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Default Dowa Markup %</label>
                        <input type="number" step="0.1" name="markup_value" class="form-control" value="{{ old('markup_value', $service->markup_value) }}" required>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <div class="form-group" style="flex: 1;">
                        <label>Minimum Quantity</label>
                        <input type="number" name="min_quantity" class="form-control" value="{{ old('min_quantity', $service->min_quantity) }}" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Maximum Quantity</label>
                        <input type="number" name="max_quantity" class="form-control" value="{{ old('max_quantity', $service->max_quantity) }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="active" {{ $service->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $service->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 2rem;">
                    <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">&larr; Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Service &rarr;</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
