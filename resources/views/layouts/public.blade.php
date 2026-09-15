<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dowa - Turnkey SMM Reseller SaaS Platform')</title>
    <meta name="description" content="@yield('meta_description', 'Launch your branded social media reseller business in minutes with Dowa turnkey reseller technology.')">
    <meta property="og:title" content="@yield('title', 'Dowa - SMM Reseller Platform')">
    <meta property="og:description" content="@yield('meta_description', 'Launch your branded social media reseller business in minutes with Dowa.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    <style>
        :root {
            --primary: #10B981;
            --primary-dark: #059669;
            --primary-soft: #D1FAE5;
            --text-heading: #064E3B;
            --bg-surface: #FAFAFA;
            --card-bg: #FFFFFF;
            --text-main: #111827;
            --text-muted: #6B7280;
            --border-color: #E5E7EB;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background-color: var(--bg-surface); color: var(--text-main); line-height: 1.6; overflow-x: hidden; }

        .container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }

        /* Navigation */
        .navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 1000; padding: 1rem 0; }
        .nav-wrapper { display: flex; align-items: center; justify-content: space-between; }
        .brand-logo { font-size: 1.5rem; font-weight: 800; color: var(--text-heading); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; letter-spacing: -0.02em; }
        .brand-badge { background: var(--primary-soft); color: var(--primary-dark); font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 999px; text-transform: uppercase; }

        .nav-links { display: flex; align-items: center; gap: 2rem; list-style: none; }
        .nav-links a { color: var(--text-main); text-decoration: none; font-weight: 500; font-size: 0.95rem; transition: color 0.2s; }
        .nav-links a:hover { color: var(--primary-dark); }

        .nav-actions { display: flex; align-items: center; gap: 1rem; }
        .btn { padding: 0.6rem 1.25rem; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; border: none; cursor: pointer; }
        .btn-outline { background: transparent; color: var(--text-main); border: 1px solid var(--border-color); }
        .btn-outline:hover { background: #F3F4F6; }
        .btn-primary { background: var(--primary); color: #FFFFFF; }
        .btn-primary:hover { background: var(--primary-dark); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25); }

        .mobile-toggle { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-main); }

        /* Footer */
        .footer { background: #064E3B; color: #ECFDF5; padding: 4rem 0 2rem; margin-top: 5rem; }
        .footer-grid { display: grid; grid-template-columns: 2fr repeat(3, 1fr); gap: 3rem; margin-bottom: 3rem; }
        .footer-brand h3 { font-size: 1.5rem; font-weight: 800; color: #FFFFFF; margin-bottom: 0.75rem; }
        .footer-brand p { color: #A7F3D0; font-size: 0.9rem; max-width: 300px; }
        .footer-col h4 { font-size: 0.95rem; font-weight: 700; color: #FFFFFF; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 0.6rem; }
        .footer-col ul li a { color: #A7F3D0; text-decoration: none; font-size: 0.9rem; transition: color 0.2s; }
        .footer-col ul li a:hover { color: #FFFFFF; }
        .footer-bottom { border-top: 1px solid rgba(167, 243, 208, 0.2); padding-top: 1.5rem; display: flex; align-items: center; justify-content: space-between; font-size: 0.85rem; color: #A7F3D0; }

        @media (max-width: 768px) {
            .nav-links, .nav-actions { display: none; }
            .mobile-toggle { display: block; }
            .nav-links.active { display: flex; flex-direction: column; position: absolute; top: 100%; left: 0; right: 0; background: #FFF; padding: 1.5rem; border-bottom: 1px solid var(--border-color); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
            .footer-grid { grid-template-columns: 1fr; gap: 2rem; }
            .footer-bottom { flex-direction: column; gap: 1rem; text-align: center; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar">
        <div class="container nav-wrapper">
            <a href="{{ route('home') }}" class="brand-logo">
                <span>Dowa</span>
                <span class="brand-badge">SaaS</span>
            </a>

            <button class="mobile-toggle" onclick="toggleMobileNav()" aria-label="Toggle Navigation">☰</button>

            <ul class="nav-links" id="mobileNav">
                <li><a href="{{ route('home') }}#features">Product</a></li>
                <li><a href="{{ route('marketing.features') }}">Features</a></li>
                <li><a href="{{ route('marketing.pricing') }}">Pricing</a></li>
                <li><a href="{{ route('marketing.resources') }}">Resources</a></li>
                <li><a href="{{ route('marketing.contact') }}">Contact</a></li>
            </ul>

            <div class="nav-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-outline">Go to Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Create Your Store</a>
                @endauth
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3>Dowa</h3>
                    <p>The turnkey multi-tenant platform empowering creators, agencies, and entrepreneurs to operate high-margin SMM reseller businesses.</p>
                </div>
                <div class="footer-col">
                    <h4>Product</h4>
                    <ul>
                        <li><a href="{{ route('marketing.features') }}">Storefronts</a></li>
                        <li><a href="{{ route('marketing.features') }}">Flexible Pricing</a></li>
                        <li><a href="{{ route('marketing.features') }}">Fulfillment</a></li>
                        <li><a href="{{ route('marketing.pricing') }}">Profit Ledger</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="{{ route('marketing.resources') }}">Documentation</a></li>
                        <li><a href="{{ route('marketing.resources') }}">API Docs</a></li>
                        <li><a href="{{ route('marketing.contact') }}">Help Center</a></li>
                        <li><a href="{{ route('marketing.contact') }}">System Status</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="{{ route('marketing.contact') }}">About Us</a></li>
                        <li><a href="{{ route('marketing.contact') }}">Contact</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div>&copy; {{ date('Y') }} Dowa Inc. All rights reserved.</div>
                <div>Built for high-performance reseller networks.</div>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileNav() {
            const nav = document.getElementById('mobileNav');
            nav.classList.toggle('active');
        }
    </script>
    @yield('scripts')
</body>
</html>
