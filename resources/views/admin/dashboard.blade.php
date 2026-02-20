@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8 animate-fade-in">
    <h1 class="admin-page-title">Dashboard</h1>
    <p class="admin-page-desc">MetaSeat platform overview</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="admin-card p-5 flex items-start justify-between gap-4 animate-fade-in" style="animation-delay: 0.05s">
        <div>
            <p class="text-[var(--meta-text-secondary)] text-sm font-medium">Total Users</p>
            <p class="text-2xl font-bold text-white mt-1 tracking-tight">{{ number_format($totalUsers) }}</p>
        </div>
        <div class="admin-stat-icon bg-[var(--meta-accent-start)]/20 text-[var(--meta-accent-end)]">
            <i data-lucide="users"></i>
        </div>
    </div>
    <div class="admin-card p-5 flex items-start justify-between gap-4 animate-fade-in" style="animation-delay: 0.1s">
        <div>
            <p class="text-[var(--meta-text-secondary)] text-sm font-medium">Total Events</p>
            <p class="text-2xl font-bold text-white mt-1 tracking-tight">{{ number_format($totalEvents) }}</p>
        </div>
        <div class="admin-stat-icon bg-blue-500/20 text-blue-400">
            <i data-lucide="calendar-days"></i>
        </div>
    </div>
    <div class="admin-card p-5 flex items-start justify-between gap-4 animate-fade-in" style="animation-delay: 0.15s">
        <div>
            <p class="text-[var(--meta-text-secondary)] text-sm font-medium">Upcoming Events</p>
            <p class="text-2xl font-bold text-white mt-1 tracking-tight">{{ number_format($upcomingEvents) }}</p>
        </div>
        <div class="admin-stat-icon bg-emerald-500/20 text-emerald-400">
            <i data-lucide="calendar-check"></i>
        </div>
    </div>
    <div class="admin-card p-5 flex items-start justify-between gap-4 animate-fade-in" style="animation-delay: 0.2s">
        <div>
            <p class="text-[var(--meta-text-secondary)] text-sm font-medium">Live Streams</p>
            <p class="text-2xl font-bold text-white mt-1 tracking-tight">{{ number_format($liveStreams) }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($liveStreams > 0)
                <span class="live-dot w-2.5 h-2.5 rounded-full animate-pulse"></span>
            @endif
            <div class="admin-stat-icon bg-[var(--meta-live)]/20 text-[var(--meta-live)]">
                <i data-lucide="radio"></i>
            </div>
        </div>
    </div>
    <div class="admin-card p-5 flex items-start justify-between gap-4 animate-fade-in" style="animation-delay: 0.25s">
        <div>
            <p class="text-[var(--meta-text-secondary)] text-sm font-medium">Live Viewers Now</p>
            <p class="text-2xl font-bold text-white mt-1 tracking-tight">{{ number_format($liveViewersCount ?? 0) }}</p>
        </div>
        <div class="admin-stat-icon bg-violet-500/20 text-violet-400">
            <i data-lucide="eye"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="admin-card p-5 flex items-start justify-between gap-4 animate-fade-in" style="animation-delay: 0.3s">
        <div>
            <p class="text-[var(--meta-text-secondary)] text-sm font-medium">Tickets Sold</p>
            <p class="text-2xl font-bold text-white mt-1 tracking-tight">{{ number_format($ticketsSold) }}</p>
        </div>
        <div class="admin-stat-icon bg-amber-500/20 text-amber-400">
            <i data-lucide="ticket"></i>
        </div>
    </div>
    <div class="admin-card p-5 flex items-start justify-between gap-4 animate-fade-in" style="animation-delay: 0.35s">
        <div>
            <p class="text-[var(--meta-text-secondary)] text-sm font-medium">Total Revenue (coins)</p>
            <p class="text-2xl font-bold text-white mt-1 tracking-tight">{{ number_format($totalRevenue) }}</p>
        </div>
        <div class="admin-stat-icon bg-[var(--meta-accent-start)]/20 text-[var(--meta-accent-end)]">
            <i data-lucide="dollar-sign"></i>
        </div>
    </div>
    <div class="admin-card p-5 flex items-start justify-between gap-4 animate-fade-in" style="animation-delay: 0.4s">
        <div>
            <p class="text-[var(--meta-text-secondary)] text-sm font-medium">Today Revenue (coins)</p>
            <p class="text-2xl font-bold text-white mt-1 tracking-tight">{{ number_format($todayRevenue) }}</p>
        </div>
        <div class="admin-stat-icon bg-cyan-500/20 text-cyan-400">
            <i data-lucide="trending-up"></i>
        </div>
    </div>
    <div class="admin-card p-5 flex items-start justify-between gap-4 animate-fade-in" style="animation-delay: 0.45s">
        <div>
            <p class="text-[var(--meta-text-secondary)] text-sm font-medium">Platform Commission</p>
            <p class="text-2xl font-bold text-white mt-1 tracking-tight">{{ number_format($totalCommission ?? 0) }}</p>
        </div>
        <div class="admin-stat-icon bg-slate-500/20 text-slate-400">
            <i data-lucide="banknote"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="admin-card p-5 animate-fade-in" style="animation-delay: 0.5s">
        <h2 class="font-semibold text-white mb-4 text-[15px]">Ticket sales (last 30 days)</h2>
        @if($ticketSalesTrend->isNotEmpty())
            <div class="h-40 flex items-end gap-1">
                @foreach($ticketSalesTrend as $day)
                    <div class="flex-1 rounded-t min-h-[4px] transition-all duration-300 hover:opacity-90" style="height: {{ max(4, min(120, $day->count * 6)) }}px; background: linear-gradient(180deg, var(--meta-accent-end) 0%, var(--meta-accent-start) 100%);" title="{{ $day->date }}: {{ $day->count }}"></div>
                @endforeach
            </div>
            <p class="text-[var(--meta-text-muted)] text-xs mt-3">By date</p>
        @else
            @include('admin.partials.empty', ['icon' => 'bar-chart-2', 'title' => 'No data yet', 'description' => 'Ticket sales will appear here over the last 30 days.'])
        @endif
    </div>
    <div class="admin-card p-5 animate-fade-in" style="animation-delay: 0.55s">
        <h2 class="font-semibold text-white mb-4 text-[15px]">Revenue trend (last 30 days)</h2>
        @if($revenueTrend->isNotEmpty())
            @php $maxRev = $revenueTrend->max('total') ?: 1; @endphp
            <div class="h-40 flex items-end gap-1">
                @foreach($revenueTrend as $day)
                    <div class="flex-1 rounded-t transition-all duration-300 hover:opacity-90" style="height: {{ max(4, round($day->total / $maxRev * 100)) }}px; background: linear-gradient(180deg, rgba(142,124,255,0.6) 0%, rgba(108,92,231,0.4) 100%);" title="{{ $day->date }}: {{ $day->total }} coins"></div>
                @endforeach
            </div>
            <p class="text-[var(--meta-text-muted)] text-xs mt-3">Coins purchased by date</p>
        @else
            @include('admin.partials.empty', ['icon' => 'trending-up', 'title' => 'No data yet', 'description' => 'Revenue from coin purchases will appear here.'])
        @endif
    </div>
    <div class="admin-card p-5 animate-fade-in" style="animation-delay: 0.6s">
        <h2 class="font-semibold text-white mb-4 text-[15px]">User growth (last 30 days)</h2>
        @if($userGrowthTrend->isNotEmpty())
            @php $maxUg = $userGrowthTrend->max('count') ?: 1; @endphp
            <div class="h-40 flex items-end gap-1">
                @foreach($userGrowthTrend as $day)
                    <div class="flex-1 rounded-t transition-all duration-300 hover:opacity-90" style="height: {{ max(4, round($day->count / $maxUg * 100)) }}px; background: linear-gradient(180deg, var(--meta-accent-end) 0%, var(--meta-accent-start) 100%);" title="{{ $day->date }}: {{ $day->count }}"></div>
                @endforeach
            </div>
            <p class="text-[var(--meta-text-muted)] text-xs mt-3">New signups by date</p>
        @else
            @include('admin.partials.empty', ['icon' => 'user-plus', 'title' => 'No data yet', 'description' => 'New signups will appear here.'])
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in" style="animation-delay: 0.65s">
    <div class="admin-card overflow-hidden">
        <h2 class="font-semibold text-white px-5 py-4 border-b border-[var(--meta-border)] text-[15px]">Recent Event Bookings</h2>
        <div class="divide-y divide-[var(--meta-border)] max-h-72 overflow-y-auto">
            @forelse($recentEventBookings as $b)
                <div class="px-5 py-3 flex justify-between items-center text-sm transition-colors hover:bg-[var(--meta-card-hover)]">
                    <span class="text-white font-medium">{{ $b->user->name ?? 'N/A' }}</span>
                    <span class="text-[var(--meta-text-secondary)]">{{ $b->event->title ?? '-' }} · {{ $b->event->date ?? '' }}</span>
                </div>
            @empty
                @include('admin.partials.empty', ['icon' => 'clipboard-list', 'title' => 'No recent event bookings', 'description' => 'Bookings will show here as users purchase tickets.'])
            @endforelse
        </div>
    </div>
    <div class="admin-card overflow-hidden">
        <h2 class="font-semibold text-white px-5 py-4 border-b border-[var(--meta-border)] text-[15px]">Recent Livestream Bookings</h2>
        <div class="divide-y divide-[var(--meta-border)] max-h-72 overflow-y-auto">
            @forelse($recentLivestreamBookings as $b)
                <div class="px-5 py-3 flex justify-between items-center text-sm transition-colors hover:bg-[var(--meta-card-hover)]">
                    <span class="text-white font-medium">{{ $b->user->name ?? 'N/A' }}</span>
                    <span class="text-[var(--meta-text-secondary)]">{{ $b->livestream->title ?? '-' }}</span>
                </div>
            @empty
                @include('admin.partials.empty', ['icon' => 'radio', 'title' => 'No recent livestream bookings', 'description' => 'Viewer bookings will appear here.'])
            @endforelse
        </div>
    </div>
</div>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
@endsection
