@extends('layouts.public')

@section('title', 'Dowa Platform Features')

@section('content')
<section class="container" style="padding: 4rem 0;">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 3rem;">
        <h1 style="font-size: 2.5rem; font-weight: 800; color: #064E3B; margin-bottom: 1rem;">Platform Features</h1>
        <p style="color: #6B7280; font-size: 1.1rem;">Explore the powerful tools Dowa provides to help you launch, manage, and scale your SMM reseller business.</p>
    </div>

    @if ($featuresSection && $featuresSection->items->count())
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem;">
            @foreach ($featuresSection->items as $item)
                <div style="background: #FFF; border: 1px solid #E5E7EB; border-radius: 12px; padding: 2rem;">
                    <div style="width: 40px; height: 44px; background: #D1FAE5; color: #059669; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; margin-bottom: 1rem;">✓</div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #111827; margin-bottom: 0.5rem;">{{ $item->title }}</h3>
                    <p style="color: #6B7280; font-size: 0.95rem;">{{ $item->description }}</p>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
