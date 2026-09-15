@extends('layouts.onboarding')

@section('title', 'Step 4: Create Storefront')

@php $currentStep = 'store'; @endphp

@section('content')
<h2>4. Configure Your Storefront</h2>
<p style="color: #6B7280; margin-bottom: 1.5rem; font-size: 0.9rem;">Set up your public reseller store name, custom URL slug, and brand identity.</p>

<form method="POST" action="{{ route('onboarding.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="form-group">
        <label>Store Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $store ? $store->name : $tenant->name) }}" required>
    </div>

    <div class="form-group">
        <label>Store URL Slug (e.g. my-smm-hub)</label>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="color: #6B7280; font-size: 0.9rem;">{{ url('/store') }}/</span>
            <input type="text" name="slug" class="form-control" value="{{ old('slug', $store ? $store->slug : $tenant->slug) }}" required>
        </div>
    </div>

    <div class="form-group">
        <label>Store Description</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Describe your SMM store services">{{ old('description', $store ? $store->description : '') }}</textarea>
    </div>

    <div class="form-group">
        <label>Store Logo (Optional)</label>
        <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
        @if ($store && $store->logo)
            <p style="font-size: 0.8rem; color: #059669; margin-top: 0.25rem;">Current logo uploaded: {{ $store->logo }}</p>
        @endif
    </div>

    <div class="btn-group">
        <a href="{{ route('onboarding.pricing') }}" class="btn btn-secondary">&larr; Back</a>
        <button type="submit" class="btn btn-primary">Save & Continue to Bank Setup &rarr;</button>
    </div>
</form>
@endsection
