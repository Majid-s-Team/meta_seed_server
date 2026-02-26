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
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/admin-modern.css'])
    @vite('resources/css/app.css')
    @stack('styles')
</head>
<body class="min-h-screen surface-main" id="adminBody">
    <div class="flex min-h-screen w-full admin-shell">
        @include('components.admin.sidebar')
        <div class="admin-sidebar-overlay" id="adminSidebarOverlay" aria-hidden="true"></div>

        <main class="admin-main flex-1 min-w-0 p-6">
            <header class="admin-topbar">
                <button type="button" class="admin-sidebar-toggle" id="adminSidebarToggle" aria-label="Open menu">
                    <i data-lucide="menu"></i>
                </button>
                <div class="admin-topbar-breadcrumb">
                    <span>@yield('breadcrumb_section', 'Main')</span>
                    <span> / </span>
                    <span>@yield('breadcrumb_page', 'Dashboard')</span>
                </div>
                <div class="admin-topbar-actions">
                    @stack('topbar_actions')
                </div>
            </header>
            @yield('content')
        </main>
    </div>
    <script>lucide.createIcons();</script>
    <script>
    (function() {
        var body = document.getElementById('adminBody') || document.body;
        var overlay = document.getElementById('adminSidebarOverlay');
        function openMenu() { body.classList.add('admin-sidebar-open'); if (overlay) { overlay.setAttribute('aria-hidden', 'false'); overlay.style.display = 'block'; } }
        function closeMenu() { body.classList.remove('admin-sidebar-open'); if (overlay) { overlay.setAttribute('aria-hidden', 'true'); overlay.style.display = ''; } }
        function toggleMenu() { body.classList.contains('admin-sidebar-open') ? closeMenu() : openMenu(); }
        document.addEventListener('click', function(e) {
            if (e.target.closest('#adminSidebarToggle')) { e.preventDefault(); toggleMenu(); return; }
            if (e.target.closest('.admin-sidebar-close') || e.target.id === 'adminSidebarOverlay') { e.preventDefault(); closeMenu(); return; }
        });
    })();
    </script>
    @stack('scripts')
</body>
</html>
