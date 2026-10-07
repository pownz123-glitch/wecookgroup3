<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Field Operations') · DASURECO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="shell">
        <aside class="rail">
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-mark">D</span>
                <span>
                    <span class="brand-name">DASURECO</span>
                    <span class="brand-sub">FIELD OPERATIONS</span>
                </span>
            </a>

            <nav aria-label="Main navigation">
                <div class="nav-label">Workspace</div>
                <div class="nav-list">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <span class="nav-dot"></span>Overview
                    </a>
                    <a class="nav-link {{ request()->routeIs('work-orders.*') ? 'active' : '' }}" href="{{ route('work-orders.index') }}">
                        <span class="nav-dot"></span>{{ auth()->user()->role === 'field' ? 'My tasks' : 'Work orders' }}
                    </a>
                    @if (auth()->user()->role !== 'field')
                        <a class="nav-link {{ request()->routeIs('assets.*') ? 'active' : '' }}" href="{{ route('assets.index') }}">
                            <span class="nav-dot"></span>Assets
                        </a>
                    @endif
                </div>
            </nav>

            <div class="rail-bottom">
                <div>{{ now()->format('D, M j, Y') }}</div>
                <div style="margin-top: 5px; color: #d4ed82">● &nbsp;Operations system online</div>
            </div>
        </aside>

        <div class="main">
            <header class="topbar">
                <div class="eyebrow">Davao del Sur Electric Cooperative</div>
                <div class="top-user">
                    <span class="avatar">{{ str($userName = auth()->user()->name)->substr(0, 1)->upper() }}</span>
                    <span>
                        <span class="user-name">{{ $userName }}</span>
                        <span class="user-role">{{ str_replace('_', ' ', auth()->user()->role) }}</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout" type="submit">Sign out</button>
                    </form>
                </div>
            </header>

            <main class="content">
                @if (session('status'))
                    <div class="flash" role="status">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="error-box" role="alert">
                        <strong>Check the highlighted information.</strong>
                        <ul style="margin: 6px 0 0; padding-left: 18px">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>