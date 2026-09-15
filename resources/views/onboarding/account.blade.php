@extends('layouts.onboarding')

@section('title', 'Step 1: Account Info')

@php $currentStep = 'account'; @endphp

@section('content')
<h2>1. Review Account Details</h2>
<p style="color: #6B7280; margin-bottom: 1.5rem; font-size: 0.9rem;">Verify your personal contact information associated with your reseller account.</p>

<form method="POST" action="{{ route('onboarding.account') }}">
    @csrf
    <div class="form-group">
        <label>First Name</label>
        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" required>
    </div>

    <div class="form-group">
        <label>Last Name</label>
        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}" required>
    </div>

    <div class="form-group">
        <label>Email Address</label>
        <input type="email" class="form-control" value="{{ $user->email }}" disabled style="background-color: #F3F4F6;">
    </div>

    <div class="form-group">
        <label>Username</label>
        <input type="text" class="form-control" value="{{ $user->username }}" disabled style="background-color: #F3F4F6;">
    </div>

    <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
    </div>

    <div class="btn-group">
        <div></div>
        <button type="submit" class="btn btn-primary">Continue to Services &rarr;</button>
    </div>
</form>
@endsection
