<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sign In') — Hospital Management System</title>
    <meta name="description" content="Hospital Management System — @yield('title', 'Sign In')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--navy-950, #050d1a);
            min-height: 100vh;
            display: flex;
        }

        .auth-split {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Left Hero Panel */
        .auth-hero {
            display: none;
            flex: 1;
            position: relative;
            background: linear-gradient(135deg, #0a1628 0%, #0d2137 40%, #0a2e33 100%);
            overflow: hidden;
            padding: 48px;
            flex-direction: column;
            justify-content: space-between;
        }
        @media (min-width: 960px) {
            .auth-hero { display: flex; }
        }

        .auth-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 20% 30%, rgba(13,110,253,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 50% 60% at 80% 70%, rgba(16,185,129,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 80% 80% at 50% 50%, rgba(10,19,40,0.8) 0%, transparent 100%);
            pointer-events: none;
        }

        .auth-hero-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            animation: floatOrb 8s ease-in-out infinite;
        }
        .auth-hero-orb-1 {
            width: 300px; height: 300px;
            background: rgba(13,110,253,0.15);
            top: -80px; left: -60px;
            animation-delay: 0s;
        }
        .auth-hero-orb-2 {
            width: 200px; height: 200px;
            background: rgba(16,185,129,0.12);
            bottom: 100px; right: -40px;
            animation-delay: -3s;
        }
        .auth-hero-orb-3 {
            width: 150px; height: 150px;
            background: rgba(13,110,253,0.1);
            bottom: -30px; left: 30%;
            animation-delay: -5s;
        }
        @keyframes floatOrb {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(-20px) scale(1.05); }
        }

        .auth-hero-content {
            position: relative;
            z-index: 1;
        }

        .auth-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(13,110,253,0.15);
            border: 1px solid rgba(13,110,253,0.3);
            border-radius: 100px;
            padding: 6px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #6ea8fe;
            letter-spacing: 0.5px;
            margin-bottom: 32px;
        }
        .auth-hero-badge-dot {
            width: 6px; height: 6px;
            background: #0d6efd;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.5; transform: scale(0.8); }
        }

        .auth-hero-logo {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 24px;
            color: white;
            margin-bottom: 28px;
            box-shadow: 0 0 40px rgba(13,110,253,0.3), 0 8px 24px rgba(0,0,0,0.4);
            letter-spacing: -0.5px;
        }

        .auth-hero h1 {
            font-size: 42px;
            font-weight: 800;
            line-height: 1.1;
            color: white;
            margin-bottom: 16px;
            letter-spacing: -1px;
        }
        .auth-hero h1 span {
            background: linear-gradient(90deg, #0d6efd, #20c997);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-hero-desc {
            font-size: 16px;
            color: rgba(255,255,255,0.55);
            line-height: 1.7;
            max-width: 380px;
            margin-bottom: 48px;
        }

        .auth-hero-features {
            position: relative;
            z-index: 1;
        }
        .auth-hero-features-title {
            font-size: 11px;
            font-weight: 600;
            color: rgba(255,255,255,0.35);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 16px;
        }
        .auth-hero-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 13px;
            color: rgba(255,255,255,0.6);
        }
        .auth-hero-feature:last-child { border-bottom: none; }
        .auth-hero-feature-icon {
            width: 28px; height: 28px;
            background: rgba(13,110,253,0.15);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #0d6efd;
        }

        /* Right Form Panel */
        .auth-panel {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 24px;
            background: #070f1c;
            position: relative;
        }
        @media (min-width: 960px) {
            .auth-panel {
                width: 480px;
                flex-shrink: 0;
                border-left: 1px solid rgba(255,255,255,0.05);
                padding: 48px 40px;
            }
        }

        .auth-panel-inner {
            width: 100%;
            max-width: 400px;
        }

        .auth-mobile-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 36px;
        }
        @media (min-width: 960px) { .auth-mobile-logo { display: none; } }

        .auth-mobile-logo-icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            color: white;
        }
        .auth-mobile-logo-text {
            font-size: 18px;
            font-weight: 700;
            color: white;
        }
        .auth-mobile-logo-sub {
            font-size: 11px;
            color: rgba(255,255,255,0.4);
        }

        .auth-heading {
            margin-bottom: 32px;
        }
        .auth-heading-title {
            font-size: 26px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }
        .auth-heading-sub {
            font-size: 14px;
            color: rgba(255,255,255,0.45);
        }

        .auth-form-group {
            margin-bottom: 18px;
        }
        .auth-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: rgba(255,255,255,0.65);
            margin-bottom: 7px;
        }

        .auth-input-wrap {
            position: relative;
        }
        .auth-input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.25);
            pointer-events: none;
        }
        .auth-input-icon-right {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.25);
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }
        .auth-input-icon-right:hover { color: rgba(255,255,255,0.6); }

        .auth-input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 11px 14px 11px 42px;
            font-size: 14px;
            color: white;
            font-family: inherit;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .auth-input.has-right-icon { padding-right: 42px; }
        .auth-input::placeholder { color: rgba(255,255,255,0.2); }
        .auth-input:focus {
            border-color: rgba(13,110,253,0.5);
            background: rgba(13,110,253,0.05);
            box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
        }
        .auth-input:focus + .auth-input-icon { color: #0d6efd; }
        .auth-input.error {
            border-color: rgba(239,68,68,0.5);
            background: rgba(239,68,68,0.05);
        }

        .auth-checkbox-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }
        .auth-checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            cursor: pointer;
        }
        .auth-checkbox-label input[type="checkbox"] {
            accent-color: #0d6efd;
            width: 15px; height: 15px;
        }
        .auth-forgot-link {
            font-size: 13px;
            color: #6ea8fe;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .auth-forgot-link:hover { color: #0d6efd; }

        .auth-btn {
            width: 100%;
            padding: 13px 20px;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
            box-shadow: 0 4px 20px rgba(13,110,253,0.25);
            letter-spacing: 0.2px;
        }
        .auth-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 28px rgba(13,110,253,0.35);
        }
        .auth-btn:active { transform: translateY(0); }

        .auth-footer-link {
            text-align: center;
            margin-top: 22px;
            font-size: 13px;
            color: rgba(255,255,255,0.4);
        }
        .auth-footer-link a {
            color: #6ea8fe;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        .auth-footer-link a:hover { color: #0d6efd; }

        .auth-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .auth-alert-error {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.25);
            color: #fca5a5;
        }
        .auth-alert-success {
            background: rgba(16,185,129,0.1);
            border: 1px solid rgba(16,185,129,0.25);
            color: #6ee7b7;
        }
    </style>
</head>
<body>
<div class="auth-split">

    <div class="auth-hero">
        <div class="auth-hero-orb auth-hero-orb-1"></div>
        <div class="auth-hero-orb auth-hero-orb-2"></div>
        <div class="auth-hero-orb auth-hero-orb-3"></div>

        <div class="auth-hero-content">
            <div>
                <div class="auth-hero-badge">
                    <div class="auth-hero-badge-dot"></div>
                    HEALTHCARE PORTAL
                </div>

                <div class="auth-hero-logo">HMS</div>

                <h1>Hospital<br><span>Management</span></h1>

                <p class="auth-hero-desc">
                    Comprehensive healthcare administration for patients, medical staff, departments, and appointment scheduling.
                </p>
            </div>
        </div>

        <div class="auth-hero-features">
            <div class="auth-hero-features-title">Core Modules</div>
            <div class="auth-hero-feature">
                <div class="auth-hero-feature-icon">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                Patient Registration & Records
            </div>
            <div class="auth-hero-feature">
                <div class="auth-hero-feature-icon">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                Doctor & Staff Management
            </div>
            <div class="auth-hero-feature">
                <div class="auth-hero-feature-icon">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                Department Organization
            </div>
            <div class="auth-hero-feature">
                <div class="auth-hero-feature-icon">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                Appointment Scheduling
            </div>
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-panel-inner">

            <div class="auth-mobile-logo">
                <div class="auth-mobile-logo-icon">HMS</div>
                <div>
                    <div class="auth-mobile-logo-text">Hospital MS</div>
                    <div class="auth-mobile-logo-sub">Management System</div>
                </div>
            </div>

            <div class="auth-heading">
                <div class="auth-heading-title">@yield('title', 'Sign In')</div>
                <div class="auth-heading-sub">@yield('subtitle', 'Welcome back to Hospital System')</div>
            </div>

            @yield('content')

        </div>
    </div>

</div>

<script>
function togglePassword(inputId, btnId) {
    const input = document.getElementById(inputId);
    const btn = document.getElementById(btnId);
    if (!input || !btn) return;
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.querySelector('.eye-open').style.display  = isHidden ? 'none' : 'block';
    btn.querySelector('.eye-closed').style.display = isHidden ? 'block' : 'none';
}
</script>
</body>
</html>
