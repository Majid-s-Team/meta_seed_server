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
    <style>
        :root {
            --meta-bg: #0B0F1A;
            --meta-sidebar: #0F1424;
            --meta-card: #141A2E;
            --meta-card-hover: #182042;
            --meta-border: rgba(255,255,255,0.06);
            --meta-accent-start: #6C5CE7;
            --meta-accent-end: #8E7CFF;
            --meta-text: #FFFFFF;
            --meta-text-secondary: #9CA3AF;
            --meta-text-muted: #6B7280;
            --meta-live: #EF4444;
        }
        body { font-family: 'Inter', sans-serif; background: var(--meta-bg); color: var(--meta-text); }
        /* Legacy compatibility */
        .bg-meta-dark { background-color: var(--meta-bg) !important; }
        .bg-meta-card { background-color: var(--meta-card) !important; }
        .text-meta-secondary { color: var(--meta-text-secondary) !important; }
        .gradient-primary {
            background: linear-gradient(135deg, var(--meta-accent-start) 0%, var(--meta-accent-end) 100%);
            box-shadow: 0 4px 20px rgba(108, 92, 231, 0.35);
        }
        .gradient-primary:hover { box-shadow: 0 6px 28px rgba(108, 92, 231, 0.45); }
        .live-dot { background-color: var(--meta-live); }

        /* Sidebar */
        .admin-sidebar {
            background: var(--meta-sidebar);
            border-right: 1px solid var(--meta-border);
        }
        .admin-sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 12px;
            color: var(--meta-text-secondary);
            transition: all 0.2s ease;
        }
        .admin-sidebar-link:hover {
            background: rgba(255,255,255,0.06);
            color: var(--meta-text);
        }
        .admin-sidebar-link.active {
            background: linear-gradient(135deg, rgba(108,92,231,0.2) 0%, rgba(142,124,255,0.15) 100%);
            color: var(--meta-text);
            border-left: 3px solid var(--meta-accent-start);
        }
        .admin-sidebar-link .lucide { width: 20px; height: 20px; flex-shrink: 0; }

        /* Cards */
        .admin-card {
            background: var(--meta-card);
            border: 1px solid var(--meta-border);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            transition: all 0.2s ease;
        }
        .admin-card:hover { background: var(--meta-card-hover); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

        /* Stat card icon bubble */
        .admin-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-stat-icon .lucide { width: 22px; height: 22px; }

        /* Buttons */
        .admin-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 500;
            background: linear-gradient(135deg, var(--meta-accent-start) 0%, var(--meta-accent-end) 100%);
            color: #fff;
            box-shadow: 0 4px 20px rgba(108, 92, 231, 0.35);
            transition: all 0.2s ease;
        }
        .admin-btn-primary:hover {
            box-shadow: 0 6px 28px rgba(108, 92, 231, 0.45);
            transform: translateY(-1px);
        }
        .admin-btn-ghost {
            padding: 8px 16px;
            border-radius: 10px;
            background: rgba(255,255,255,0.06);
            color: var(--meta-text-secondary);
            transition: all 0.2s ease;
        }
        .admin-btn-ghost:hover { background: rgba(255,255,255,0.1); color: var(--meta-text); }

        /* Tables */
        .admin-table thead { background: rgba(255,255,255,0.04); color: var(--meta-text-secondary); font-size: 0.8125rem; font-weight: 500; }
        .admin-table th { padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--meta-border); }
        .admin-table td { padding: 14px 16px; border-bottom: 1px solid var(--meta-border); }
        .admin-table tbody tr { transition: background 0.15s ease; }
        .admin-table tbody tr:hover { background: rgba(255,255,255,0.03); }

        /* Form inputs */
        .admin-input {
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--meta-border);
            color: var(--meta-text);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .admin-input::placeholder { color: var(--meta-text-muted); }
        .admin-input:focus {
            outline: none;
            border-color: var(--meta-accent-start);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
        }

        /* Empty state */
        .admin-empty {
            padding: 48px 24px;
            text-align: center;
        }
        .admin-empty .lucide { color: var(--meta-text-muted); margin: 0 auto 16px; }
        .admin-empty-title { font-weight: 600; color: var(--meta-text); margin-bottom: 4px; }
        .admin-empty-desc { font-size: 0.875rem; color: var(--meta-text-secondary); }

        /* Page header */
        .admin-page-title { font-size: 1.5rem; font-weight: 700; color: var(--meta-text); letter-spacing: -0.02em; }
        .admin-page-desc { font-size: 0.875rem; color: var(--meta-text-secondary); margin-top: 2px; }

        /* Animations */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.3s ease forwards; }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen">
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
                <a href="{{ route('admin.livestreams.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.livestreams.*') ? 'active' : '' }}">
                    <i data-lucide="radio"></i>
                    <span>Livestreams</span>
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
