@extends('layouts.public')

@section('title', 'Dowa Resources & Guides')

@section('content')
<section class="container" style="padding: 4rem 0;">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 3rem;">
        <h1 style="font-size: 2.5rem; font-weight: 800; color: #064E3B; margin-bottom: 1rem;">Resources & Knowledge Base</h1>
        <p style="color: #6B7280; font-size: 1.1rem;">Guides, tutorials, and documentation to help you build a successful SMM reseller network.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
        <div style="background: #FFF; border: 1px solid #E5E7EB; border-radius: 12px; padding: 2rem;">
            <h3 style="font-weight: 700; color: #111827; margin-bottom: 0.5rem;">Reseller Onboarding Guide</h3>
            <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 1rem;">Step-by-step instructions on setting up your store, selecting services, and connecting payouts.</p>
            <a href="{{ route('register') }}" style="color: #059669; font-weight: 600; text-decoration: none;">Get Started &rarr;</a>
        </div>
        <div style="background: #FFF; border: 1px solid #E5E7EB; border-radius: 12px; padding: 2rem;">
            <h3 style="font-weight: 700; color: #111827; margin-bottom: 0.5rem;">API Documentation</h3>
            <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 1rem;">Technical specifications for connecting external client apps and custom domains.</p>
            <a href="#" style="color: #059669; font-weight: 600; text-decoration: none;">Read Docs &rarr;</a>
        </div>
        <div style="background: #FFF; border: 1px solid #E5E7EB; border-radius: 12px; padding: 2rem;">
            <h3 style="font-weight: 700; color: #111827; margin-bottom: 0.5rem;">Marketing & Scaling Tips</h3>
            <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 1rem;">Strategies for attracting high-volume clients and growing your monthly recurring revenue.</p>
            <a href="#" style="color: #059669; font-weight: 600; text-decoration: none;">Learn More &rarr;</a>
        </div>
    </div>
</section>
@endsection
