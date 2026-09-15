@extends('layouts.onboarding')

@section('title', 'Step 6: Review & Launch')

@php $currentStep = 'review'; @endphp

@section('content')
<h2>6. Review Your Setup</h2>
<p style="color: #6B7280; margin-bottom: 1.5rem; font-size: 0.9rem;">Review your store configuration before activating your reseller empire.</p>

<div style="display: flex; flex-direction: column; gap: 1.25rem; margin-bottom: 2rem;">
    <div style="border: 1px solid #E5E7EB; border-radius: 8px; padding: 1rem;">
        <h4 style="color: #064E3B; margin-bottom: 0.5rem;">Account Profile</h4>
        <p style="font-size: 0.9rem;"><strong>Name:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
        <p style="font-size: 0.9rem;"><strong>Email:</strong> {{ $user->email }}</p>
        <p style="font-size: 0.9rem;"><strong>Username:</strong> {{ $user->username }}</p>
    </div>

    <div style="border: 1px solid #E5E7EB; border-radius: 8px; padding: 1rem;">
        <h4 style="color: #064E3B; margin-bottom: 0.5rem;">Selected Services & Pricing</h4>
        <p style="font-size: 0.9rem;"><strong>Active Services Selected:</strong> {{ $tenantServices->count() }} services</p>
    </div>

    <div style="border: 1px solid #E5E7EB; border-radius: 8px; padding: 1rem;">
        <h4 style="color: #064E3B; margin-bottom: 0.5rem;">Storefront</h4>
        <p style="font-size: 0.9rem;"><strong>Store Name:</strong> {{ $store ? $store->name : 'N/A' }}</p>
        <p style="font-size: 0.9rem;"><strong>Store Slug / URL:</strong> {{ $store ? url('/store/'.$store->slug) : 'N/A' }}</p>
    </div>

    <div style="border: 1px solid #E5E7EB; border-radius: 8px; padding: 1rem;">
        <h4 style="color: #064E3B; margin-bottom: 0.5rem;">Payout Bank Account</h4>
        <p style="font-size: 0.9rem;"><strong>Bank:</strong> {{ $bank ? $bank->bank_name : 'N/A' }}</p>
        <p style="font-size: 0.9rem;"><strong>Account Number:</strong> {{ $bank ? '••••'.substr($bank->account_number, -4) : 'N/A' }}</p>
        <p style="font-size: 0.9rem;"><strong>Account Name:</strong> {{ $bank ? $bank->account_name : 'N/A' }}</p>
    </div>
</div>

<form method="POST" action="{{ route('onboarding.review') }}">
    @csrf

    <div class="btn-group">
        <a href="{{ route('onboarding.bank') }}" class="btn btn-secondary">&larr; Back</a>
        <button type="submit" class="btn btn-primary" style="background: #059669; font-size: 1rem; padding: 0.8rem 2rem;">🚀 Create My Store & Complete Setup</button>
    </div>
</form>
@endsection
