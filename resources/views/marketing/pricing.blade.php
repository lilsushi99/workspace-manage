@extends('layouts.public')

@section('title', 'Dowa Reseller Model & Pricing')

@section('content')
<section class="container" style="padding: 4rem 0;">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 3rem;">
        <h1 style="font-size: 2.5rem; font-weight: 800; color: #064E3B; margin-bottom: 1rem;">Simple Reseller Economics</h1>
        <p style="color: #6B7280; font-size: 1.1rem;">You set your own markup on catalog services. You keep 100% of your retail profit margin.</p>
    </div>

    <div style="max-width: 800px; margin: 0 auto; background: #FFF; border: 1px solid #E5E7EB; border-radius: 12px; padding: 2.5rem;">
        <h3 style="color: #064E3B; font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">How Dowa Profit Margins Work</h3>
        <p style="color: #4B5563; margin-bottom: 1.5rem;">For example: An Instagram Likes service has a Dowa provider base price of $0.45 / 1k. You set a 50% markup during onboarding. Your storefront lists the service at $0.675 / 1k. When a customer orders 10k likes ($6.75 total), you earn $2.25 profit directly credited to your Dowa wallet ledger.</p>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="{{ route('register') }}" class="btn btn-primary" style="padding: 0.8rem 2rem; font-size: 1rem;">Start Reselling Now &rarr;</a>
        </div>
    </div>
</section>
@endsection
