<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — MetaSeat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/admin-modern.css'])
    @vite('resources/css/app.css')
    @stack('styles')
</head>
<body class="min-h-screen surface-main">
    <div class="flex">
        <aside class="admin-sidebar">
            {{-- Brand Header --}}
            <div class="admin-sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-logo">
                    <div class="admin-sidebar-logo-mark">
                        <i data-lucide="zap" style="width:18px;height:18px;color:#fff;"></i>
                    </div>
                    <span class="admin-sidebar-logo-text">MetaSeat</span>
                    <span class="admin-sidebar-logo-badge">Admin</span>
                </a>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 p-3 overflow-y-auto" style="scrollbar-width: thin;">
                <div class="admin-nav-label">Main</div>

                <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.events.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                    <i data-lucide="calendar-days"></i>
                    <span>Events</span>
                </a>
                <a href="{{ route('admin.categories.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i data-lucide="folder"></i>
                    <span>Categories</span>
                </a>
                <a href="{{ route('admin.livestreams.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.livestreams.*') ? 'active' : '' }}">
                    <i data-lucide="radio"></i>
                    <span>Livestreams</span>
                </a>
                <a href="{{ route('admin.recordings.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.recordings.*') ? 'active' : '' }}">
                    <i data-lucide="video"></i>
                    <span>Recordings</span>
                </a>
                <a href="{{ route('admin.bookings.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                    <i data-lucide="clipboard-list"></i>
                    <span>Bookings</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i data-lucide="users"></i>
                    <span>Users</span>
                </a>
                <a href="{{ route('admin.transactions.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                    <i data-lucide="wallet"></i>
                    <span>Transactions</span>
                </a>
                <a href="{{ route('admin.cms.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.cms.*') ? 'active' : '' }}">
                    <i data-lucide="file-text"></i>
                    <span>CMS</span>
                </a>
            </nav>

            {{-- Footer: user info + logout --}}
            <div class="admin-sidebar-footer">
                @auth
                <div class="flex items-center gap-2.5 py-2.5 px-3 rounded-[10px] bg-white/[0.03] border border-white/[0.05] mb-1.5">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0" style="background:var(--grad-brand);">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                    <div class="overflow-hidden flex-1 min-w-0">
                        <div class="text-[0.8rem] font-semibold text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</div>
                        <div class="text-[0.7rem] truncate" style="color:var(--meta-text-muted);">{{ auth()->user()->email ?? '' }}</div>
                    </div>
                </div>
                @endauth

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="admin-sidebar-link logout-btn w-full text-left">
                        <i data-lucide="log-out"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 min-h-screen p-6" style="margin-left: 256px; position: relative; z-index: 1;">
            @yield('content')
        </main>
    </div>
    <script>lucide.createIcons();</script>
    @stack('scripts')
</body>
</html>
