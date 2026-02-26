{{-- Use from layout: breadcrumb is via @yield in layout. Use this component with vars when needed: @include('components.admin.topbar', ['breadcrumb_section' => 'Main', 'breadcrumb_page' => 'Page']) --}}
<header class="admin-topbar">
    <div class="admin-topbar-breadcrumb">
        <span>{{ $breadcrumb_section ?? 'Main' }}</span>
        <span> / </span>
        <span>{{ $breadcrumb_page ?? 'Dashboard' }}</span>
    </div>
    <div class="admin-topbar-actions">
        @stack('topbar_actions')
    </div>
</header>
