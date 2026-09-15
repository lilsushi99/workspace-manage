<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Onboarding') - Dowa SMM Reseller</title>
    <style>
        :root {
            --primary: #10B981;
            --primary-dark: #059669;
            --bg-surface: #FAFAFA;
            --card-bg: #FFFFFF;
            --text-main: #111827;
            --text-muted: #6B7280;
            --border-color: #E5E7EB;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        body { background-color: var(--bg-surface); color: var(--text-main); min-height: 100vh; padding: 2rem 1rem; display: flex; flex-direction: column; align-items: center; }
        .wizard-container { width: 100%; max-width: 800px; }
        .header { text-align: center; margin-bottom: 2rem; }
        .header h1 { font-size: 1.875rem; font-weight: 700; color: #064E3B; margin-bottom: 0.5rem; }
        .header p { color: var(--text-muted); font-size: 0.95rem; }
        .progress-bar { display: flex; justify-content: space-between; position: relative; margin-bottom: 2.5rem; }
        .progress-bar::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 3px; background: var(--border-color); z-index: 1; transform: translateY(-50%); }
        .step-item { position: relative; z-index: 2; background: var(--bg-surface); padding: 0 0.5rem; text-align: center; }
        .step-circle { width: 36px; height: 36px; border-radius: 50%; background: #E5E7EB; color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem; margin: 0 auto 0.25rem; transition: all 0.2s; }
        .step-item.active .step-circle { background: var(--primary); color: #FFF; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2); }
        .step-item.completed .step-circle { background: var(--primary-dark); color: #FFF; }
        .step-label { font-size: 0.75rem; font-weight: 500; color: var(--text-muted); }
        .step-item.active .step-label { color: var(--primary-dark); font-weight: 600; }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-weight: 500; margin-bottom: 0.35rem; font-size: 0.875rem; }
        .form-control { width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.95rem; }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15); }
        .btn-group { display: flex; justify-content: space-between; margin-top: 2rem; }
        .btn { padding: 0.65rem 1.5rem; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; font-size: 0.95rem; border: none; }
        .btn-primary { background: var(--primary); color: #FFF; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: #E5E7EB; color: var(--text-main); }
        .btn-secondary:hover { background: #D1D5DB; }
        .alert-error { background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.875rem; }
        .empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-muted); }
    </style>
</head>
<body>
    <div class="wizard-container">
        <div class="header">
            <h1>Dowa Reseller Store Setup</h1>
            <p>Complete these setup steps to launch your social media reseller business</p>
        </div>

        @php
            $currentStep = $currentStep ?? 'account';
            $steps = ['account' => 'Account', 'services' => 'Services', 'pricing' => 'Pricing', 'store' => 'Store', 'bank' => 'Bank', 'review' => 'Review'];
            $stepKeys = array_keys($steps);
            $currentIndex = array_search($currentStep, $stepKeys);
        @endphp

        <div class="progress-bar">
            @foreach ($steps as $key => $label)
                @php
                    $idx = array_search($key, $stepKeys);
                    $class = '';
                    if ($key === $currentStep) { $class = 'active'; }
                    elseif ($idx !== false && $idx < $currentIndex) { $class = 'completed'; }
                @endphp
                <div class="step-item {{ $class }}">
                    <div class="step-circle">{{ $idx + 1 }}</div>
                    <div class="step-label">{{ $label }}</div>
                </div>
            @endforeach
        </div>

        @if ($errors->any())
            <div class="alert-error">
                <ul style="margin-left: 1rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            @yield('content')
        </div>
    </div>
</body>
</html>
