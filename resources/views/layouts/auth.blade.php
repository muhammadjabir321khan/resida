<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Residia') }} — {{ __('Sign in') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }
        .auth-shell { display: flex; min-height: 100vh; flex-direction: column; }
        @media (min-width: 1024px) { .auth-shell { flex-direction: row; } }

        .auth-brand {
            position: relative;
            overflow: hidden;
            background: linear-gradient(145deg, #0c4a6e 0%, #0f766e 45%, #115e59 100%);
            color: #fff;
            padding: 2rem 1.5rem;
        }
        .auth-brand::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 80%, rgba(255,255,255,0.08) 0%, transparent 50%),
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: auto, 32px 32px, 32px 32px;
            pointer-events: none;
        }
        @media (min-width: 1024px) {
            .auth-brand {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                width: 44%;
                padding: 3rem 4rem;
            }
        }
        @media (min-width: 1280px) { .auth-brand { width: 48%; padding: 4rem; } }
        .auth-brand__inner { position: relative; z-index: 1; }
        .auth-brand__logo {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            color: #fff;
            text-decoration: none;
        }
        .auth-brand__logo-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .auth-brand__logo-text { font-size: 1.25rem; font-weight: 700; }
        .auth-brand__hero { margin-top: 2rem; max-width: 28rem; }
        @media (min-width: 1024px) { .auth-brand__hero { margin-top: 0; } }
        .auth-brand__title { font-size: clamp(1.75rem, 3vw, 2.25rem); font-weight: 700; line-height: 1.2; }
        .auth-brand__subtitle { margin-top: 1rem; color: rgba(204,251,241,0.9); line-height: 1.6; }
        .auth-brand__list { margin-top: 2.5rem; list-style: none; display: flex; flex-direction: column; gap: 1rem; }
        .auth-brand__list li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.875rem;
            color: rgba(240,253,250,0.9);
        }
        .auth-brand__check {
            flex-shrink: 0;
            width: 1.5rem;
            height: 1.5rem;
            margin-top: 0.1rem;
            border-radius: 9999px;
            background: rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-brand__footer {
            position: relative;
            z-index: 1;
            margin-top: 2rem;
            font-size: 0.75rem;
            color: rgba(153,246,228,0.6);
        }
        @media (min-width: 1024px) { .auth-brand__footer { margin-top: 0; } }
        .auth-brand__desktop-only { display: none; }
        @media (min-width: 1024px) {
            .auth-brand__desktop-only { display: flex; flex-direction: column; justify-content: space-between; flex: 1; }
            .auth-brand__mobile-only { display: none; }
        }

        .auth-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f8fafc;
        }
        .auth-main__center {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
        }
        @media (min-width: 640px) { .auth-main__center { padding: 2.5rem 2rem; } }

        .auth-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
        }
        @media (min-width: 640px) { .auth-card { padding: 2.5rem; } }
        .auth-card__title { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
        .auth-card__subtitle { margin-top: 0.5rem; font-size: 0.875rem; color: #64748b; }

        .auth-alert {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }
        .auth-alert--success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .auth-alert--error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .auth-alert ul { margin: 0; padding-left: 1.25rem; }

        .auth-form { margin-top: 1.75rem; display: flex; flex-direction: column; gap: 1.25rem; }
        .auth-field__label {
            display: block;
            margin-bottom: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #334155;
        }
        .auth-field__wrap { position: relative; }
        .auth-field__icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1.25rem;
            height: 1.25rem;
            color: #94a3b8;
            pointer-events: none;
        }
        .auth-input {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            font-size: 0.875rem;
            color: #0f172a;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
        }
        .auth-input::placeholder { color: #94a3b8; }
        .auth-input:focus {
            background: #fff;
            border-color: #0f766e;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.15);
        }
        .auth-field__error { margin-top: 0.375rem; font-size: 0.8125rem; color: #dc2626; }

        .auth-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .auth-remember {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #475569;
            cursor: pointer;
        }
        .auth-remember input { width: 1rem; height: 1rem; accent-color: #0f766e; }
        .auth-link { font-size: 0.875rem; font-weight: 500; color: #0f766e; text-decoration: none; }
        .auth-link:hover { color: #115e59; text-decoration: underline; }

        .auth-btn {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.8125rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, #0f766e 0%, #0c4a6e 100%);
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(15, 118, 110, 0.25);
            transition: transform 0.15s, box-shadow 0.15s, background 0.15s;
        }
        .auth-btn:hover {
            background: linear-gradient(135deg, #0d9488 0%, #075985 100%);
            box-shadow: 0 10px 24px rgba(15, 118, 110, 0.3);
        }
        .auth-btn svg { width: 1.25rem; height: 1.25rem; }

        .auth-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.875rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <aside class="auth-brand">
            <div class="auth-brand__mobile-only auth-brand__inner">
                <a href="{{ url('/') }}" class="auth-brand__logo">
                    <span class="auth-brand__logo-icon">
                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </span>
                    <span class="auth-brand__logo-text">{{ config('app.name', 'Residia') }}</span>
                </a>
            </div>

            <div class="auth-brand__desktop-only">
                <div class="auth-brand__inner">
                    <a href="{{ url('/') }}" class="auth-brand__logo">
                        <span class="auth-brand__logo-icon">
                            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </span>
                        <span class="auth-brand__logo-text">{{ config('app.name', 'Residia') }}</span>
                    </a>
                </div>

                <div class="auth-brand__inner auth-brand__hero">
                    <h1 class="auth-brand__title">{{ __('Manage your rentals with confidence') }}</h1>
                    <p class="auth-brand__subtitle">{{ __('Track properties, leases, rent payments, and maintenance — all in one place.') }}</p>
                    <ul class="auth-brand__list">
                        <li>
                            <span class="auth-brand__check"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
                            {{ __('Portfolio dashboard & rent roll reports') }}
                        </li>
                        <li>
                            <span class="auth-brand__check"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
                            {{ __('Tenant portal & automated rent reminders') }}
                        </li>
                        <li>
                            <span class="auth-brand__check"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg></span>
                            {{ __('Maintenance tracking & income/expense ledger') }}
                        </li>
                    </ul>
                </div>

                <p class="auth-brand__footer">&copy; {{ date('Y') }} {{ config('app.name', 'Residia') }}. {{ __('All rights reserved.') }}</p>
            </div>
        </aside>

        <main class="auth-main">
            <div class="auth-main__center">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
