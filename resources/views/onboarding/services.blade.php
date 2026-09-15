@extends('layouts.onboarding')

@section('title', 'Step 2: Choose Services')

@php $currentStep = 'services'; @endphp

@section('content')
<h2>2. Select Services for Your Store</h2>
<p style="color: #6B7280; margin-bottom: 1.5rem; font-size: 0.9rem;">Choose the social media growth services you want to sell on your storefront.</p>

<form method="POST" action="{{ route('onboarding.services') }}">
    @csrf

    @if ($groupedServices->isEmpty())
        <div class="empty-state">
            <h3>No Active Services Available</h3>
            <p style="margin-top: 0.5rem;">Dowa is currently updating the service catalogue. Please check back shortly.</p>
        </div>
    @else
        @foreach ($groupedServices as $platform => $categories)
            <div style="margin-bottom: 2rem; border-bottom: 1px solid #E5E7EB; padding-bottom: 1.5rem;">
                <h3 style="color: #064E3B; margin-bottom: 1rem;">{{ $platform }} Services</h3>
                @foreach ($categories as $category => $services)
                    <div style="margin-bottom: 1rem; margin-left: 0.5rem;">
                        <h4 style="font-size: 0.95rem; color: #374151; margin-bottom: 0.5rem;">{{ $category }}</h4>
                        @foreach ($services as $service)
                            <label style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; border: 1px solid #E5E7EB; border-radius: 6px; margin-bottom: 0.5rem; cursor: pointer; background: #FFF;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <input type="checkbox" name="services[]" value="{{ $service->id }}" {{ in_array($service->id, $selectedServiceIds) ? 'checked' : '' }}>
                                    <div>
                                        <div style="font-weight: 600; font-size: 0.9rem;">{{ $service->name }}</div>
                                        <div style="font-size: 0.75rem; color: #6B7280;">Min: {{ number_format($service->min_quantity) }} | Max: {{ number_format($service->max_quantity) }}</div>
                                    </div>
                                </div>
                                <div style="font-weight: 700; color: #059669; font-size: 0.9rem;">
                                    ${{ number_format($service->base_price, 4) }} / k
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif

    <div class="btn-group">
        <a href="{{ route('onboarding.account') }}" class="btn btn-secondary">&larr; Back</a>
        <button type="submit" class="btn btn-primary">Save & Continue to Pricing &rarr;</button>
    </div>
</form>
@endsection
