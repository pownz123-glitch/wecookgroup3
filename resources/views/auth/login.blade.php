<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · DASURECO Field Operations</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="login-page">
        <section class="login-aside">
            <a class="brand" href="{{ route('login') }}">
                <span class="brand-mark">D</span>
                <span>
                    <span class="brand-name">DASURECO</span>
                    <span class="brand-sub">FIELD OPERATIONS</span>
                </span>
            </a>
            <div>
                <div class="eyebrow" style="color: #d4ed82; margin-bottom: 17px">Field operations · Work management</div>
                <h1>Powering work<br>in the field.</h1>
                <p>Coordinate crews, track service work, and keep critical electrical assets in view.</p>
            </div>
            <div class="eyebrow" style="color: #9db5aa">Davao del Sur Electric Cooperative</div>
        </section>
        <section class="login-main">
            <div class="login-box">
                <div class="eyebrow" style="margin-bottom: 13px">Secure workspace</div>
                <h2>Welcome back</h2>
                <p class="subtitle">Sign in with your DASURECO account.</p>

                @if ($errors->any())
                    <div class="error-box" role="alert" style="margin-top: 20px">{{ $errors->first() }}</div>
                @endif

                <form class="login-form" method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <div class="field">
                        <label for="email">Work email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required>
                    </div>
                    <label style="display: flex; align-items: center; gap: 8px; color: var(--muted); font-size: 11px">
                        <input type="checkbox" name="remember" value="1"> Keep me signed in
                    </label>
                    <button class="btn btn-primary" type="submit">Sign in <span aria-hidden="true">→</span></button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>