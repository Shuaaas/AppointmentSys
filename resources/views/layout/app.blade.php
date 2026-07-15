<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') – HR Recruitment</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link rel="stylesheet" href="{{ asset('css/page-loader.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
{{-- CDN: Axios (CSRF headers for AJAX) --}}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    window.axios = axios;
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
</script>
</head>
<body>

<nav class="hr-sidebar" id="hrSidebar" aria-label="Main navigation">
    <div class="hr-sidebar-brand">
        <img src="{{ asset('deped_logo.png') }}" alt="DepEd logo" class="hr-sidebar-logo" />
    </div>

    <div class="hr-sidebar-links">
        @if (auth()->user()?->isAdmin() || auth()->user()?->isHr() || auth()->user()?->isManager())
            <a href="{{ route('dashboard.index') }}" class="nav-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}" data-tooltip="Dashboard">
                <!-- <i class="ti ti-layout-dashboard" aria-hidden="true"></i> -->
                <i class="bi bi-grid-1x2 dashboard-icon" aria-hidden="true"></i>
                <span class="nav-label">Dashboard</span>
            </a>
        @endif
        @if (auth()->user()?->isAdmin())
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') && !request()->routeIs('admin.users.create') ? 'active' : '' }}" data-tooltip="Manage Users">
                <i class="ti ti-users" aria-hidden="true"></i>
                <span class="nav-label">Manage Users</span>
            </a>
            <a href="{{ route('admin.users.create') }}" class="nav-link {{ request()->routeIs('admin.users.create') ? 'active' : '' }}" data-tooltip="Add User">
                <i class="ti ti-user-plus" aria-hidden="true"></i>
                <span class="nav-label">Add User</span>
            </a>
            <a href="{{ route('admin.passwords.index') }}" class="nav-link {{ request()->routeIs('admin.passwords.*') ? 'active' : '' }}" data-tooltip="Reset Passwords">
                <i class="ti ti-key" aria-hidden="true"></i>
                <span class="nav-label">Reset Passwords</span>
            </a>
        @else
            <a href="{{ route('appointments.index') }}" class="nav-link {{ request()->routeIs('appointments.index') || request()->routeIs('appointments.show') ? 'active' : '' }}" data-tooltip="{{ auth()->user()?->isRecords() ? 'Transaction Numbers' : 'Appointment Data' }}">
                <i class="ti ti-file-description" aria-hidden="true"></i>
                <span class="nav-label">
                    @if(auth()->user()?->isRecords())
                        Transaction Numbers
                    @else
                        Appointment Data
                    @endif
                </span>
            </a>
            @if (auth()->user()?->isHr())
                <div class="nav-section-title">New Entry</div>
                <a href="{{ route('appointments.create') }}" class="nav-link {{ request()->routeIs('appointments.create') ? 'active' : '' }}" data-tooltip="New Appointment">
                    <i class="ti ti-file-plus" aria-hidden="true"></i>
                    <span class="nav-label">New Appointment</span>
                </a>
            @endif
            <div class="nav-section-title">Records</div>
            <a href="{{ route('history.index') }}" class="nav-link {{ request()->routeIs('history.*') ? 'active' : '' }}" data-tooltip="History">
                <i class="ti ti-archive" aria-hidden="true"></i>
                <span class="nav-label">History</span>
            </a>
            <a href="{{ route('appointments.archive') }}" class="nav-link {{ request()->routeIs('appointments.archive') ? 'active' : '' }}" data-tooltip="Archive">
                <i class="ti ti-box" aria-hidden="true"></i>
                <span class="nav-label">Archive</span>
            </a>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit" class="nav-link logout-button" data-tooltip="Logout">
                <i class="ti ti-logout" aria-hidden="true"></i>
                <span class="nav-label">Logout</span>
            </button>
        </form>
    </div>
</nav>

<div class="main">
    <header class="hr-header">
        <div class="hr-header-left">
            <button type="button" class="btn-icon" id="hrSidebarToggleBtn" aria-label="Toggle sidebar">
                <i class="ti ti-menu" aria-hidden="true" title="Toggle sidebar"></i>
            </button>
            <a href="{{ route('dashboard.index') }}" class="btn-icon" aria-label="Go to dashboard">
                <i class="ti ti-home" aria-hidden="true" title="Go to dashboard"></i>
            </a>
        </div>

        <span class="hr-header-datetime" id="hrHeaderDateTime"></span>

        <div class="hr-header-right">
            <button type="button" class="btn-icon" id="hrFullscreenBtn" aria-label="Toggle fullscreen">
                <i class="ti ti-arrows-maximize" aria-hidden="true" title="Toggle fullscreen"></i>
            </button>

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
        </div>
    </header>

    <div class="hr-pagebar">
        <h5 class="hr-page-title">@yield('title', 'Dashboard')</h5>
    </div>

    <main class="content">
        @yield('content')
    </main>
</div>

@stack('modals')
@stack('scripts')
<script>
(function () {
    const sidebar = document.getElementById('hrSidebar');
    const toggleBtn = document.getElementById('hrSidebarToggleBtn');
    const STORAGE_KEY = 'hrSidebarCollapsed';

    if (!sidebar || !toggleBtn) return;

    const MOBILE_BREAKPOINT = 900;
    let desktopCollapsed = localStorage.getItem(STORAGE_KEY) === 'true';

    function isMobile() {
        return window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT}px)`).matches;
    }

    const toggleIcon = toggleBtn.querySelector('i');

    function updateToggleIcon() {
        if (!toggleIcon) return;
        if (isMobile()) {
            toggleIcon.className = sidebar.classList.contains('visible') ? 'ti ti-x' : 'ti ti-menu';
        } else {
            toggleIcon.className = desktopCollapsed ? 'ti ti-layout-sidebar-right' : 'ti ti-menu';
        }
    }

    function updateToggleState() {
        if (isMobile()) {
            toggleBtn.classList.toggle('active', sidebar.classList.contains('visible'));
        } else {
            toggleBtn.classList.toggle('active', desktopCollapsed);
        }
    }

    function applyState() {
        if (isMobile()) {
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('visible');
            toggleBtn.setAttribute('aria-label', 'Open sidebar');
        } else {
            sidebar.classList.toggle('collapsed', desktopCollapsed);
            sidebar.classList.remove('visible');
            toggleBtn.setAttribute('aria-label', desktopCollapsed ? 'Expand sidebar' : 'Collapse sidebar');
        }
        updateToggleIcon();
        updateToggleState();
        sidebar.classList.toggle('logo-only', desktopCollapsed && !isMobile());
    }

    applyState();

    toggleBtn.addEventListener('click', function () {
        if (isMobile()) {
            const visible = sidebar.classList.toggle('visible');
            toggleBtn.setAttribute('aria-label', visible ? 'Close sidebar' : 'Open sidebar');
            updateToggleIcon();
        } else {
            desktopCollapsed = !desktopCollapsed;
            localStorage.setItem(STORAGE_KEY, desktopCollapsed ? 'true' : 'false');
            applyState();
        }
    });

    window.addEventListener('resize', applyState);
})();

(function () {
    const fullscreenBtn = document.getElementById('hrFullscreenBtn');
    if (!fullscreenBtn) return;

    const icon = fullscreenBtn.querySelector('i');

    function updateIcon() {
        if (!icon) return;
        icon.className = document.fullscreenElement ? 'ti ti-arrows-minimize' : 'ti ti-arrows-maximize';
    }

    fullscreenBtn.addEventListener('click', function () {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(function () {});
        } else {
            document.exitFullscreen();
        }
    });

    document.addEventListener('fullscreenchange', updateIcon);
    updateIcon();
})();

(function () {
    const el = document.getElementById('hrHeaderDateTime');
    if (!el) return;

    function update() {
        const now = new Date();
        const datePart = now.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
        const timePart = now.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });
        el.textContent = datePart + ' at ' + timePart;
    }

    update();
    setInterval(update, 1000);
})();
</script>
<script src="{{ asset('js/page-loader.js') }}"></script>
</body>
</html>