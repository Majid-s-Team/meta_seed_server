@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="animate-fade-in">
    <div class="mb-8">
        <p class="section-eyebrow">Overview</p>
        <h1 class="admin-page-title">Dashboard</h1>
        <p class="admin-page-desc">MetaSeat platform overview</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 section-gap animate-stagger">
        <div class="admin-card stat-card p-5">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-card-label">Total Users</div>
                    <div class="stat-card-value">{{ number_format($totalUsers) }}</div>
                </div>
                <div class="admin-stat-icon stat-icon-purple">
                    <i data-lucide="users"></i>
                </div>
            </div>
        </div>
        <div class="admin-card stat-card p-5">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-card-label">Total Events</div>
                    <div class="stat-card-value">{{ number_format($totalEvents) }}</div>
                </div>
                <div class="admin-stat-icon stat-icon-blue">
                    <i data-lucide="calendar-days"></i>
                </div>
            </div>
        </div>
        <div class="admin-card stat-card p-5">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-card-label">Upcoming Events</div>
                    <div class="stat-card-value">{{ number_format($upcomingEvents) }}</div>
                </div>
                <div class="admin-stat-icon stat-icon-green">
                    <i data-lucide="calendar-check"></i>
                </div>
            </div>
        </div>
        <div class="admin-card stat-card p-5">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-card-label">Live Streams</div>
                    <div class="stat-card-value">{{ number_format($liveStreams) }}</div>
                </div>
                <div class="flex items-center gap-2">
                    @if($liveStreams > 0)
                        <span class="live-dot w-2.5 h-2.5 rounded-full animate-pulse"></span>
                    @endif
                    <div class="admin-stat-icon stat-icon-red">
                        <i data-lucide="radio"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="admin-card stat-card p-5">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-card-label">Live Viewers Now</div>
                    <div class="stat-card-value">{{ number_format($liveViewersCount ?? 0) }}</div>
                </div>
                <div class="admin-stat-icon stat-icon-purple">
                    <i data-lucide="eye"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 section-gap animate-stagger">
        <div class="admin-card stat-card p-5">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-card-label">Tickets Sold</div>
                    <div class="stat-card-value">{{ number_format($ticketsSold) }}</div>
                </div>
                <div class="admin-stat-icon stat-icon-amber">
                    <i data-lucide="ticket"></i>
                </div>
            </div>
        </div>
        <div class="admin-card stat-card p-5">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-card-label">Total Revenue (coins)</div>
                    <div class="stat-card-value">{{ number_format($totalRevenue) }}</div>
                </div>
                <div class="admin-stat-icon stat-icon-purple">
                    <i data-lucide="dollar-sign"></i>
                </div>
            </div>
        </div>
        <div class="admin-card stat-card p-5">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-card-label">Today Revenue (coins)</div>
                    <div class="stat-card-value">{{ number_format($todayRevenue) }}</div>
                </div>
                <div class="admin-stat-icon stat-icon-cyan">
                    <i data-lucide="trending-up"></i>
                </div>
            </div>
        </div>
        <div class="admin-card stat-card p-5">
            <div class="stat-card-inner">
                <div>
                    <div class="stat-card-label">Platform Commission</div>
                    <div class="stat-card-value">{{ number_format($totalCommission ?? 0) }}</div>
                </div>
                <div class="admin-stat-icon bg-slate-500/20 text-slate-400">
                    <i data-lucide="banknote"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 section-gap">
        <div class="admin-card p-5 animate-fade-in" style="animation-delay: 0.05s">
            <h2 class="card-header-title mb-4">Ticket sales (last 30 days)</h2>
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
        <div class="admin-card p-5 animate-fade-in" style="animation-delay: 0.1s">
            <h2 class="card-header-title mb-4">Revenue trend (last 30 days)</h2>
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
        <div class="admin-card p-5 animate-fade-in" style="animation-delay: 0.15s">
            <h2 class="card-header-title mb-4">User growth (last 30 days)</h2>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 section-gap">
        <div class="admin-card overflow-hidden">
            <div class="card-header">
                <h3 class="card-header-title">Recent Event Bookings</h3>
                <div class="card-header-actions"></div>
            </div>
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
            <div class="card-header">
                <h3 class="card-header-title">Recent Livestream Bookings</h3>
                <div class="card-header-actions"></div>
            </div>
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
</div>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
@endsection
