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
        <aside class="w-64 min-h-screen admin-sidebar flex flex-col fixed z-30">
            <div class="p-5 border-b border-[rgba(255,255,255,0.06)]">
                <a href="{{ route('admin.dashboard') }}" class="font-bold text-lg tracking-tight text-white">MetaSeat</a>
                <span class="text-[var(--meta-text-muted)] text-xs block mt-0.5">Admin Panel</span>
            </div>
            <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
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
            <div class="p-3 border-t border-[rgba(255,255,255,0.06)]">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="admin-sidebar-link w-full text-left hover:!text-red-400 hover:!bg-red-500/10 !border-0">
                        <i data-lucide="log-out"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>
        <main class="flex-1 ml-64 p-6 min-h-screen">
            @yield('content')
        </main>
    </div>
    <script>lucide.createIcons();</script>
    @stack('scripts')
</body>
</html>
