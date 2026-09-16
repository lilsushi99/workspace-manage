<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $store->name }} - Social Growth Store</title>
    <style>
        :root {
            --primary: {{ $store->primary_color ?: '#10B981' }};
            --primary-dark: #059669;
            --bg-surface: #FAFAFA;
            --card-bg: #FFFFFF;
            --text-main: #111827;
            --text-muted: #6B7280;
            --border-color: #E5E7EB;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background: var(--bg-surface); color: var(--text-main); min-height: 100vh; padding-bottom: 4rem; }
        .store-header { background: #FFFFFF; border-bottom: 1px solid var(--border-color); padding: 2rem 0; text-align: center; }
        .container { max-width: 1000px; margin: 0 auto; padding: 0 1.5rem; }
        .store-title { font-size: 2rem; font-weight: 800; color: #064E3B; margin-bottom: 0.5rem; }
        .store-desc { color: var(--text-muted); font-size: 1rem; max-width: 600px; margin: 0 auto; }
        .grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2.5rem; }
        .service-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; cursor: pointer; transition: all 0.2s; }
        .service-card:hover, .service-card.selected { border-color: var(--primary); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15); }
        .order-panel { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; position: sticky; top: 2rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; }
        .form-control { width: 100%; padding: 0.65rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.95rem; }
        .btn-order { width: 100%; background: var(--primary); color: #FFF; padding: 0.8rem; border-radius: 8px; font-weight: 700; font-size: 1rem; border: none; cursor: pointer; margin-top: 1rem; }
        .btn-order:hover { background: var(--primary-dark); }
        .summary-box { background: #F3F4F6; padding: 1rem; border-radius: 8px; margin-top: 1rem; font-size: 0.9rem; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header class="store-header">
        <div class="container">
            @if ($store->logo)
                <img src="{{ asset('storage/'.$store->logo) }}" alt="{{ $store->name }}" style="max-height: 60px; margin-bottom: 1rem;">
            @endif
            <h1 class="store-title">{{ $store->name }}</h1>
            <p class="store-desc">{{ $store->description ?: 'Premium social media growth and engagement services.' }}</p>
        </div>
    </header>

    <div class="container">
        <div class="grid">
            <div>
                <h3 style="margin-bottom: 1rem; color: #064E3B;">Available Services</h3>
                @if ($groupedServices->isEmpty())
                    <div style="background: #FFF; padding: 3rem; text-align: center; border-radius: 12px; border: 1px solid #E5E7EB; color: #6B7280;">
                        No services are currently available on this storefront.
                    </div>
                @else
                    @foreach ($groupedServices as $platform => $tenantServices)
                        <h4 style="margin: 1.5rem 0 0.75rem; color: #374151;">{{ $platform }}</h4>
                        @foreach ($tenantServices as $ts)
                            <div class="service-card" onclick="selectService({{ $ts->id }}, '{{ addslashes($ts->service->name) }}', {{ $ts->selling_price }}, {{ $ts->service->min_quantity }}, {{ $ts->service->max_quantity }})">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div style="font-weight: 700; font-size: 1rem;">{{ $ts->service->name }}</div>
                                    <div style="font-weight: 800; color: #059669; font-size: 1rem;">${{ number_format($ts->selling_price, 4) }} / 1k</div>
                                </div>
                                <div style="font-size: 0.8rem; color: #6B7280; margin-top: 0.25rem;">
                                    Min: {{ number_format($ts->service->min_quantity) }} • Max: {{ number_format($ts->service->max_quantity) }}
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                @endif
            </div>

            <div>
                <div class="order-panel">
                    <h3 style="margin-bottom: 1rem; color: #064E3B;">Order Summary</h3>
                    <form method="GET" action="{{ route('storefront.checkout', $tenant->owner ? $tenant->owner->username : $store->slug) }}">
                        <div class="form-group">
                            <label>Selected Service</label>
                            <input type="text" id="selected_service_name" class="form-control" value="Select a service on left" readonly style="background: #F3F4F6;">
                            <input type="hidden" id="selected_tenant_service_id" name="tenant_service_id" required>
                        </div>

                        <div class="form-group">
                            <label>Target URL / Handle</label>
                            <input type="text" name="target" id="target_input" class="form-control" placeholder="e.g. https://instagram.com/p/123" required oninput="recalculateSummary()">
                        </div>

                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="quantity" id="quantity_input" class="form-control" value="1000" min="1" required oninput="recalculateSummary()">
                        </div>

                        <div class="summary-box">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Rate / 1k:</span>
                                <strong id="summary_rate">$0.00</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 1.1rem; color: #059669;">
                                <span>Total Price:</span>
                                <strong id="summary_total">$0.00</strong>
                            </div>
                        </div>

                        <button type="submit" class="btn-order">Proceed to Checkout &rarr;</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentRate = 0;
        let currentMin = 1;
        let currentMax = 1000000;

        function selectService(id, name, rate, min, max) {
            document.getElementById('selected_tenant_service_id').value = id;
            document.getElementById('selected_service_name').value = name;
            currentRate = rate;
            currentMin = min;
            currentMax = max;
            document.getElementById('quantity_input').value = min;
            document.getElementById('summary_rate').textContent = '$' + rate.toFixed(4);
            recalculateSummary();
        }

        function recalculateSummary() {
            const serviceId = document.getElementById('selected_tenant_service_id').value;
            const qty = parseInt(document.getElementById('quantity_input').value) || 0;

            if (!serviceId) {
                document.getElementById('summary_total').textContent = '$0.0000';
                return;
            }

            const total = (currentRate / 1000) * qty;
            document.getElementById('summary_total').textContent = '$' + total.toFixed(4);
        }
    </script>
</body>
</html>
