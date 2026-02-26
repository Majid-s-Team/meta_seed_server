<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-logo">
            <div class="admin-sidebar-logo-mark">
                <i data-lucide="zap" style="width:18px;height:18px;color:#fff;"></i>
            </div>
            <span class="admin-sidebar-logo-text">MetaSeat</span>
            <span class="admin-sidebar-logo-badge">ADMIN</span>
        </a>
        <button type="button" class="admin-sidebar-close" aria-label="Close menu">
            <i data-lucide="x"></i>
        </button>
    </div>

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

    <div class="admin-sidebar-footer">
        @auth
        <div class="admin-sidebar-user-pill flex items-center gap-2.5 py-2.5 px-3 rounded-[10px] bg-white/[0.03] border border-white/[0.05] mb-1.5">
            <div class="admin-sidebar-user-avatar w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0" style="background:var(--grad-brand);">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
            <div class="overflow-hidden flex-1 min-w-0">
                <div class="admin-sidebar-user-name text-[0.8rem] font-semibold text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</div>
                <div class="admin-sidebar-user-email text-[0.7rem] truncate" style="color:var(--meta-text-muted);">{{ auth()->user()->email ?? '' }}</div>
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
