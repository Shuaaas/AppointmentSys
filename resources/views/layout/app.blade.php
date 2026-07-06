<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') – HR Recruitment</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <i class="ti ti-users" aria-hidden="true"></i>
        <span>HR Appointment</span>
    </div>
    <nav class="sidebar-nav" aria-label="Main navigation">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="ti ti-layout-dashboard" aria-hidden="true"></i> Dashboard
        </a>
        <a href="{{ route('appointments.index') }}" class="nav-item {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
            <i class="ti ti-file-description" aria-hidden="true"></i> Appointment Data
        </a>
        <a href="{{ route('history.index') }}" class="nav-item {{ request()->routeIs('history.*') ? 'active' : '' }}">
            <i class="ti ti-archive" aria-hidden="true"></i> History
        </a>
    </nav>
    <div class="sidebar-collapse" id="sidebar-collapse-btn">
        <i class="ti ti-chevron-left" aria-hidden="true"></i> Collapse
    </div>
</aside>

<div class="main">
    <header class="topbar">
        <h1 class="topbar-title">@yield('title', 'Dashboard')</h1>
        <div class="topbar-user">
            <i class="ti ti-user-circle" aria-hidden="true"></i>
            {{ auth()->user()?->name ?? 'HR Staff' }}
        </div>
    </header>

    <div class="content">
        @if (session('success'))
            <div class="alert alert-success" role="status">
                <i class="ti ti-circle-check" aria-hidden="true"></i> {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                <i class="ti ti-alert-circle" aria-hidden="true"></i>
                <div>
                    <strong>Please check the form:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </div>
</div>

@stack('modals')
@stack('scripts')
</body>
</html>