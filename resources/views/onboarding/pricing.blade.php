@extends('layouts.onboarding')

@section('title', 'Step 3: Set Pricing & Markups')

@php $currentStep = 'pricing'; @endphp

@section('content')
<h2>3. Configure Service Pricing</h2>
<p style="color: #6B7280; margin-bottom: 1.5rem; font-size: 0.9rem;">Set your profit percentage markup over Dowa base costs.</p>

<form method="POST" action="{{ route('onboarding.pricing') }}">
    @csrf

    <div style="background: #F3F4F6; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <label style="font-weight: 600; display: block; margin-bottom: 0.5rem;">Quick Global Markup %</label>
        <div style="display: flex; gap: 0.5rem;">
            <input type="number" id="global_markup_input" name="global_markup" class="form-control" placeholder="e.g. 30" min="0" max="1000" style="max-width: 150px;">
            <button type="button" class="btn btn-secondary" onclick="applyGlobalMarkup()">Apply to All</button>
        </div>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem;">
        <thead>
            <tr style="border-bottom: 2px solid #E5E7EB; text-align: left; font-size: 0.85rem; color: #6B7280;">
                <th style="padding: 0.5rem;">Service Name</th>
                <th style="padding: 0.5rem;">Base Cost</th>
                <th style="padding: 0.5rem;">Markup %</th>
                <th style="padding: 0.5rem;">Selling Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tenantServices as $tService)
                <tr style="border-bottom: 1px solid #E5E7EB;">
                    <td style="padding: 0.75rem 0.5rem; font-weight: 500; font-size: 0.9rem;">
                        {{ $tService->service->name }}
                    </td>
                    <td style="padding: 0.75rem 0.5rem; color: #6B7280; font-size: 0.9rem;">
                        ${{ number_format($tService->service->base_price, 4) }}
                    </td>
                    <td style="padding: 0.75rem 0.5rem;">
                        <input type="number" name="markup[{{ $tService->id }}]" class="form-control markup-input" data-base="{{ $tService->service->base_price }}" value="{{ old('markup.'.$tService->id, $tService->markup_value) }}" min="0" max="1000" step="1" style="max-width: 100px;" oninput="updatePrice(this)">
                    </td>
                    <td style="padding: 0.75rem 0.5rem; font-weight: 700; color: #059669; font-size: 0.9rem;" class="selling-price-display">
                        ${{ number_format($tService->selling_price, 4) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        function updatePrice(input) {
            const base = parseFloat(input.getAttribute('data-base')) || 0;
            const markup = parseFloat(input.value) || 0;
            const selling = base * (1 + (markup / 100));
            const row = input.closest('tr');
            row.querySelector('.selling-price-display').textContent = '$' + selling.toFixed(4);
        }

        function applyGlobalMarkup() {
            const val = document.getElementById('global_markup_input').value;
            if (val !== '') {
                document.querySelectorAll('.markup-input').forEach(input => {
                    input.value = val;
                    updatePrice(input);
                });
            }
        }
    </script>

    <div class="btn-group">
        <a href="{{ route('onboarding.services') }}" class="btn btn-secondary">&larr; Back</a>
        <button type="submit" class="btn btn-primary">Save & Continue to Store Setup &rarr;</button>
    </div>
</form>
@endsection
