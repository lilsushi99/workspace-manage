@extends('layouts.public')

@section('title', 'Dowa - Build Your Own Social Media Reseller Business')

@section('styles')
<style>
    /* Hero Section */
    .hero-section { padding: 5rem 0 4rem; text-align: center; position: relative; overflow: hidden; }
    .hero-container { max-width: 900px; margin: 0 auto; }
    .hero-badge-container { display: flex; justify-content: center; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
    .hero-profile-card { background: #FFFFFF; border: 1px solid var(--border-color); border-radius: 999px; padding: 0.35rem 1rem 0.35rem 0.4rem; display: inline-flex; align-items: center; gap: 0.6rem; box-shadow: 0 2px 4px rgba(0,0,0,0.03); }
    .hero-avatar { width: 28px; height: 28px; border-radius: 50%; background: var(--primary-soft); color: var(--primary-dark); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; }
    .hero-profile-name { font-weight: 600; font-size: 0.85rem; color: var(--text-main); }
    .hero-profile-role { font-size: 0.75rem; color: var(--text-muted); }

    .hero-title { font-size: 3.25rem; font-weight: 800; color: var(--text-heading); line-height: 1.15; letter-spacing: -0.03em; margin-bottom: 1.25rem; }
    .hero-subtitle { font-size: 1.2rem; color: var(--text-muted); max-width: 700px; margin: 0 auto 2.5rem; }
    .hero-ctas { display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 3rem; flex-wrap: wrap; }
    .btn-hero-lg { padding: 0.85rem 2rem; font-size: 1.05rem; border-radius: 10px; }

    /* Social Proof Section */
    .social-proof { background: #FFFFFF; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 2.5rem 0; text-align: center; }
    .proof-label { font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 1.5rem; }
    .platforms-grid { display: flex; justify-content: center; align-items: center; gap: 2.5rem; flex-wrap: wrap; }
    .platform-pill { display: flex; align-items: center; gap: 0.5rem; background: var(--bg-surface); border: 1px solid var(--border-color); padding: 0.5rem 1.25rem; border-radius: 999px; font-weight: 600; font-size: 0.9rem; color: var(--text-heading); }

    /* Feature Grid Section */
    .section-padding { padding: 5rem 0; }
    .section-header { text-align: center; max-width: 700px; margin: 0 auto 3.5rem; }
    .section-title { font-size: 2.25rem; font-weight: 800; color: var(--text-heading); letter-spacing: -0.02em; margin-bottom: 0.75rem; }
    .section-desc { color: var(--text-muted); font-size: 1.05rem; }

    .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
    .feature-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 2rem; transition: transform 0.2s, box-shadow 0.2s; }
    .feature-card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.05); }
    .feature-icon { width: 44px; height: 44px; background: var(--primary-soft); color: var(--primary-dark); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; }
    .feature-card h3 { font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; }
    .feature-card p { font-size: 0.9rem; color: var(--text-muted); }

    /* How It Works Section */
    .how-container { background: #FFFFFF; border: 1px solid var(--border-color); border-radius: 16px; padding: 3rem 2rem; }
    .steps-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-top: 2.5rem; }
    .step-card { text-align: center; padding: 1rem; }
    .step-number { width: 36px; height: 36px; background: var(--text-heading); color: #FFFFFF; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; margin: 0 auto 1rem; }
    .step-card h4 { font-size: 0.95rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-heading); }
    .step-card p { font-size: 0.8rem; color: var(--text-muted); }

    /* Testimonials & Stats */
    .testimonials-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; }
    .testimonial-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 2rem; }
    .testimonial-quote { font-size: 1rem; color: var(--text-main); font-style: italic; margin-bottom: 1.25rem; line-height: 1.6; }
    .testimonial-author { font-weight: 700; color: var(--text-heading); font-size: 0.95rem; }
    .testimonial-role { font-size: 0.8rem; color: var(--text-muted); }

    .stats-bar { background: var(--text-heading); color: #FFFFFF; border-radius: 16px; padding: 3rem 2rem; margin-top: 4rem; display: grid; grid-template-columns: repeat(4, 1fr); text-align: center; gap: 2rem; }
    .stat-number { font-size: 2.5rem; font-weight: 800; color: #34D399; margin-bottom: 0.25rem; }
    .stat-label { font-size: 0.85rem; color: #A7F3D0; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }

    /* Final CTA */
    .cta-banner { background: linear-gradient(135deg, #064E3B 0%, #047857 100%); color: #FFFFFF; border-radius: 16px; padding: 4rem 2rem; text-align: center; margin-top: 5rem; }
    .cta-banner h2 { font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; }
    .cta-banner p { color: #A7F3D0; max-width: 600px; margin: 0 auto 2rem; font-size: 1.1rem; }

    @media (max-width: 992px) {
        .features-grid { grid-template-columns: repeat(2, 1fr); }
        .steps-grid { grid-template-columns: repeat(2, 1fr); }
        .hero-title { font-size: 2.5rem; }
        .stats-bar { grid-template-columns: repeat(2, 1fr); }
        .testimonials-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 576px) {
        .features-grid, .steps-grid, .stats-bar { grid-template-columns: 1fr; }
        .hero-title { font-size: 2rem; }
    }
</style>
@endsection

@section('content')
@php
    $hero = $sections->get('hero');
    $socialProof = $sections->get('social_proof');
    $features = $sections->get('features');
    $howItWorks = $sections->get('how_it_works');
    $testimonials = $sections->get('testimonials');
    $statistics = $sections->get('statistics');
    $finalCta = $sections->get('final_cta');
@endphp

<!-- Hero Section -->
<section class="hero-section">
    <div class="container hero-container">
        @if ($hero && $hero->items->count())
            <div class="hero-badge-container">
                @foreach ($hero->items as $item)
                    <div class="hero-profile-card">
                        <div class="hero-avatar">{{ strtoupper(substr($item->title, 0, 1)) }}</div>
                        <div>
                            <span class="hero-profile-name">{{ $item->title }}</span>
                            <span class="hero-profile-role"> • {{ $item->description }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <h1 class="hero-title">{{ $hero ? $hero->title : 'Build your own social media reseller business.' }}</h1>
        <p class="hero-subtitle">{{ $hero ? $hero->description : 'Dowa provides turnkey multi-tenant technology enabling agencies and creators to launch storefronts with automated fulfillment and profit management.' }}</p>

        <div class="hero-ctas">
            <a href="{{ route('register') }}" class="btn btn-primary btn-hero-lg">Create Your Store &rarr;</a>
            <a href="#how-it-works" class="btn btn-outline btn-hero-lg">See How It Works</a>
        </div>
    </div>
</section>

<!-- Social Proof Section -->
@if ($socialProof && $socialProof->items->count())
<section class="social-proof">
    <div class="container">
        <div class="proof-label">{{ $socialProof->title }}</div>
        <div class="platforms-grid">
            @foreach ($socialProof->items as $item)
                <div class="platform-pill">
                    <span>⚡</span>
                    <span>{{ $item->title }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Features Section -->
@if ($features)
<section class="section-padding" id="features">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">{{ $features->title }}</h2>
            <p class="section-desc">{{ $features->description }}</p>
        </div>

        @if ($features->items->count())
            <div class="features-grid">
                @foreach ($features->items as $item)
                    <div class="feature-card">
                        <div class="feature-icon">✓</div>
                        <h3>{{ $item->title }}</h3>
                        <p>{{ $item->description }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif

<!-- How It Works Section -->
@if ($howItWorks)
<section class="section-padding" id="how-it-works">
    <div class="container">
        <div class="how-container">
            <div class="section-header" style="margin-bottom: 2rem;">
                <h2 class="section-title">{{ $howItWorks->title }}</h2>
                <p class="section-desc">{{ $howItWorks->description }}</p>
            </div>

            @if ($howItWorks->items->count())
                <div class="steps-grid">
                    @foreach ($howItWorks->items as $idx => $step)
                        <div class="step-card">
                            <div class="step-number">{{ $idx + 1 }}</div>
                            <h4>{{ $step->title }}</h4>
                            <p>{{ $step->description }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endif

<!-- Testimonials & Statistics -->
@if ($testimonials || $statistics)
<section class="section-padding">
    <div class="container">
        @if ($testimonials && $testimonials->items->count())
            <div class="section-header">
                <h2 class="section-title">{{ $testimonials->title }}</h2>
                <p class="section-desc">{{ $testimonials->description }}</p>
            </div>

            <div class="testimonials-grid">
                @foreach ($testimonials->items as $item)
                    <div class="testimonial-card">
                        <p class="testimonial-quote">"{{ $item->description }}"</p>
                        <div class="testimonial-author">{{ $item->title }}</div>
                        <div class="testimonial-role">{{ $item->icon }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($statistics && $statistics->items->count())
            <div class="stats-bar">
                @foreach ($statistics->items as $stat)
                    <div>
                        <div class="stat-number">{{ $stat->title }}</div>
                        <div class="stat-label">{{ $stat->description }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif

<!-- Final CTA Banner -->
<section class="container">
    <div class="cta-banner">
        <h2>{{ $finalCta ? $finalCta->title : 'Ready to build your own social media store?' }}</h2>
        <p>{{ $finalCta ? $finalCta->description : 'Launch your branded storefront in minutes with Dowa turnkey reseller technology.' }}</p>
        <a href="{{ route('register') }}" class="btn btn-primary btn-hero-lg" style="background: #FFFFFF; color: #064E3B;">Create Your Store Now &rarr;</a>
    </div>
</section>
@endsection
