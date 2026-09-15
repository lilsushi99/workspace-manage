@extends('layouts.public')

@section('title', 'Contact Dowa Support')

@section('content')
<section class="container" style="padding: 4rem 0;">
    <div style="max-width: 600px; margin: 0 auto; background: #FFF; border: 1px solid #E5E7EB; border-radius: 12px; padding: 2.5rem;">
        <h1 style="font-size: 2rem; font-weight: 800; color: #064E3B; margin-bottom: 0.5rem; text-align: center;">Contact Dowa Team</h1>
        <p style="color: #6B7280; font-size: 0.95rem; text-align: center; margin-bottom: 2rem;">Have questions about setting up your reseller store or enterprise features? Drop us a message.</p>

        <form method="POST" action="#">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.35rem; font-size: 0.875rem;">Your Name</label>
                <input type="text" class="form-control" style="width: 100%; padding: 0.65rem; border: 1px solid #E5E7EB; border-radius: 6px;" required>
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.35rem; font-size: 0.875rem;">Email Address</label>
                <input type="email" class="form-control" style="width: 100%; padding: 0.65rem; border: 1px solid #E5E7EB; border-radius: 6px;" required>
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.35rem; font-size: 0.875rem;">Message</label>
                <textarea rows="4" class="form-control" style="width: 100%; padding: 0.65rem; border: 1px solid #E5E7EB; border-radius: 6px;" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 1rem;">Send Message &rarr;</button>
        </form>
    </div>
</section>
@endsection
