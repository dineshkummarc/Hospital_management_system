<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d6efd">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Hospital MS">
    <title>@yield('title', 'Dashboard') — Hospital Management System</title>
    <meta name="description" content="Hospital Management System">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
<div class="app-shell">

    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="sidebar-logo">
            <div class="sidebar-logo-icon">HMS</div>
            <div class="sidebar-logo-text">
                <span class="sidebar-logo-title">Hospital MS</span>
                <span class="sidebar-logo-sub">Healthcare Admin</span>
            </div>
        </a>

        <nav class="sidebar-nav">
            <span class="sidebar-section-label">Main Modules</span>

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            {{-- Patients --}}
            <a href="{{ route('patients.index') }}" class="nav-item {{ request()->routeIs('patients.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Patients
            </a>

            {{-- Doctors --}}
            <a href="{{ route('doctors.index') }}" class="nav-item {{ request()->routeIs('doctors.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Doctors
            </a>

            {{-- Departments --}}
            <a href="{{ route('departments.index') }}" class="nav-item {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Departments
            </a>

            {{-- Appointments --}}
            <a href="{{ route('appointments.index') }}" class="nav-item {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Appointments
            </a>

            {{-- System Administration --}}
            @if(auth()->user()->isAdmin())
            <hr class="divider">
            <span class="sidebar-section-label">Administration</span>
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Users & Privileges
            </a>
            <a href="{{ route('admin.audit-logs') }}" class="nav-item {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Audit Logs
            </a>
            <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Settings
            </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <a href="{{ route('profile.edit') }}" class="user-card">
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="user-avatar">
                <div>
                    <div class="user-name">{{ Str::words(auth()->user()->name, 2, '') }}</div>
                    <div class="user-role">
                        <span class="badge badge-{{ auth()->user()->role_color }}" style="font-size:10px;padding:1px 6px;">
                            {{ auth()->user()->role_label }}
                        </span>
                    </div>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin-top:8px;">
                @csrf
                <button type="submit" class="btn btn-secondary w-full" style="width:100%;justify-content:center;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    {{-- Mobile overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    {{-- Main --}}
    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-btn" onclick="toggleSidebar()" style="display:none;" id="menuBtn">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <div class="page-title">@yield('page-title', 'Dashboard')</div>
                    @hasSection('breadcrumb')
                    <nav class="breadcrumb-nav">
                        <ul>
                            <li><a href="{{ route('dashboard') }}">HMS</a></li>
                            @php
                                $breadcrumb = trim(View::getSection('breadcrumb'));
                                $crumbs = explode(' / ', $breadcrumb);
                            @endphp
                            @foreach($crumbs as $crumb)
                                <li>
                                    @if(str_contains($crumb, 'href='))
                                        {!! $crumb !!}
                                    @else
                                        <span style="font-size: 14px; color: var(--text-muted);">{{ $crumb }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                    @endif
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('profile.edit') }}" class="topbar-btn" title="Profile">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </a>
            </div>
        </header>

        <main class="page-body">
            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="alert alert-success animate-in">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-error animate-in">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').style.display = 'block';
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').style.display = 'none';
}
if (window.innerWidth <= 768) {
    document.getElementById('menuBtn').style.display = 'flex';
}
window.addEventListener('resize', () => {
    document.getElementById('menuBtn').style.display = window.innerWidth <= 768 ? 'flex' : 'none';
});
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => {
        el.style.transition = 'opacity 0.5s ease';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 5000);
</script>
@stack('scripts')
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('[SW] Registered:', reg.scope))
            .catch(err => console.warn('[SW] Registration failed:', err));
    });
}
</script>
</body>
</html>
