@extends('layouts.onboarding')

@section('title', 'Step 5: Bank Details')

@php $currentStep = 'bank'; @endphp

@section('content')
<h2>5. Payout Bank Details</h2>
<p style="color: #6B7280; margin-bottom: 1.5rem; font-size: 0.9rem;">Provide your bank account details for future profit payouts and withdrawals.</p>

<form method="POST" action="{{ route('onboarding.bank') }}">
    @csrf

    <div class="form-group">
        <label>Bank Name</label>
        <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $bank ? $bank->bank_name : '') }}" placeholder="e.g. GTBank, Zenith, Chase" required>
    </div>

    <div class="form-group">
        <label>Account Number</label>
        <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $bank ? $bank->account_number : '') }}" placeholder="10-digit account number" required>
    </div>

    <div class="form-group">
        <label>Account Name</label>
        <input type="text" name="account_name" class="form-control" value="{{ old('account_name', $bank ? $bank->account_name : '') }}" placeholder="Account holder name" required>
    </div>

    <div style="background: #FFFBEB; border: 1px solid #FCD34D; color: #92400E; padding: 0.75rem; border-radius: 6px; font-size: 0.85rem; margin-bottom: 1.5rem;">
        <strong>Verification Status:</strong> {{ $bank ? ucfirst($bank->verification_status) : 'Pending Verification' }}
    </div>

    <div class="btn-group">
        <a href="{{ route('onboarding.store') }}" class="btn btn-secondary">&larr; Back</a>
        <button type="submit" class="btn btn-primary">Save & Continue to Final Review &rarr;</button>
    </div>
</form>
@endsection
