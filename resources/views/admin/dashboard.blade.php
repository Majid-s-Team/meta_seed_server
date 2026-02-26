@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <div>
            <div class="page-eyebrow">Overview</div>
            <h1 class="page-title">Dashboard</h1>
        </div>
    </div>

    {{-- Stats Row 1 --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Users</span>
                <div class="stat-icon i-purple">
                    <i data-lucide="users"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($totalUsers) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Events</span>
                <div class="stat-icon i-purple">
                    <i data-lucide="calendar-days"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($totalEvents) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Upcoming Events</span>
                <div class="stat-icon i-green">
                    <i data-lucide="calendar-check"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($upcomingEvents) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Live Streams</span>
                <div class="stat-icon i-red">
                    <i data-lucide="radio"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($liveStreams) }}</div>
        </div>
    </div>

    {{-- Stats Row 2 --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Live Viewers Now</span>
                <div class="stat-icon i-purple">
                    <i data-lucide="eye"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($liveViewersCount ?? 0) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Tickets Sold</span>
                <div class="stat-icon i-orange">
                    <i data-lucide="ticket"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($ticketsSold) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Total Revenue</span>
                <div class="stat-icon i-green">
                    <i data-lucide="dollar-sign"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($totalRevenue) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Today Revenue</span>
                <div class="stat-icon i-purple">
                    <i data-lucide="trending-up"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($todayRevenue) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">Commission</span>
                <div class="stat-icon i-orange">
                    <i data-lucide="banknote"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($totalCommission ?? 0) }}</div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="charts-row">
        <div class="chart-card">
            <div class="chart-head">
                <div class="chart-title">Ticket Sales</div>
                <div class="chart-subtitle">Last 30 days</div>
            </div>
            @if($ticketSalesTrend->isNotEmpty() && $ticketSalesTrend->count() >= 5)
                @php $maxTicket = max(1, $ticketSalesTrend->max('count')); @endphp
                <div class="chart-body">
                    <div class="sparkline-bars">
                        @foreach($ticketSalesTrend as $day)
                            <div class="sp-bar {{ $day->count >= $maxTicket * 0.9 ? 'hi' : '' }}" style="height: {{ max(4, min(100, round($day->count / $maxTicket * 100))) }}%" title="{{ $day->date }}: {{ $day->count }}"></div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="chart-empty">
                    <i data-lucide="bar-chart-2"></i>
                    <div class="chart-empty-label">No data yet</div>
                </div>
            @endif
        </div>

        <div class="chart-card">
            <div class="chart-head">
                <div class="chart-title">Revenue Trend</div>
                <div class="chart-subtitle">Coin purchases · Last 30 days</div>
            </div>
            @if($revenueTrend->isNotEmpty() && $revenueTrend->count() >= 5)
                @php $maxRev = $revenueTrend->max('total') ?: 1; @endphp
                <div class="chart-body">
                    <div class="sparkline-bars">
                        @foreach($revenueTrend as $day)
                            <div class="sp-bar {{ $day->total >= $maxRev * 0.9 ? 'hi' : '' }}" style="height: {{ max(4, min(100, round($day->total / $maxRev * 100))) }}%" title="{{ $day->date }}: {{ $day->total }} coins"></div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="chart-empty">
                    <i data-lucide="trending-up"></i>
                    <div class="chart-empty-label">No revenue data yet</div>
                </div>
            @endif
        </div>

        <div class="chart-card">
            <div class="chart-head">
                <div class="chart-title">User Growth</div>
                <div class="chart-subtitle">New signups · Last 30 days</div>
            </div>
            @if($userGrowthTrend->isNotEmpty() && $userGrowthTrend->count() >= 5)
                @php $maxUg = $userGrowthTrend->max('count') ?: 1; @endphp
                <div class="chart-body">
                    <div class="bar-chart-wrap">
                        @foreach($userGrowthTrend as $day)
                            <div class="bc-bar {{ $day->count >= $maxUg * 0.8 ? 'peak' : '' }}" style="height: {{ max(4, min(100, round($day->count / $maxUg * 100))) }}%" title="{{ $day->date }}: {{ $day->count }}"></div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="chart-empty">
                    <i data-lucide="user-plus"></i>
                    <div class="chart-empty-label">No data yet</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="bottom-row">
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Recent Event Bookings</span>
                <a href="{{ route('admin.bookings.event') }}" class="panel-link">View all →</a>
            </div>
            <div class="tbl-head bk-grid">
                <span>User</span><span>Event</span><span>Date</span>
            </div>
            @forelse($recentEventBookings as $b)
                <div class="tbl-row bk-grid">
                    <div class="user-pair">
                        <div class="mini-avatar">{{ strtoupper(substr($b->user->name ?? 'U', 0, 1)) }}</div>
                        <span class="cell-primary" style="font-size:11.5px">{{ $b->user->name ?? 'N/A' }}</span>
                    </div>
                    <span class="cell-primary" style="font-size:11.5px">{{ $b->event->title ?? '-' }}</span>
                    <span class="cell-dim">{{ $b->event->date ?? '' }}</span>
                </div>
            @empty
                <div class="empty-block">
                    <div class="empty-ico">
                        <i data-lucide="clipboard-list"></i>
                    </div>
                    <div class="empty-title">No recent event bookings</div>
                    <div class="empty-sub">Bookings will show here as users purchase tickets.</div>
                </div>
            @endforelse
            @if($recentEventBookings->isNotEmpty())
                <div style="padding:8px 16px; border-top:1px solid var(--ref-border-subtle)">
                    <span style="font-size:10.5px; color:var(--ref-text-muted)">{{ $recentEventBookings->count() }} booking(s) total</span>
                </div>
            @endif
        </div>

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Recent Livestream Bookings</span>
                <a href="{{ route('admin.bookings.livestream') }}" class="panel-link">View all →</a>
            </div>
            @if($recentLivestreamBookings->isNotEmpty())
                <div class="tbl-head bk-grid">
                    <span>User</span><span>Livestream</span><span>Date</span>
                </div>
                @foreach($recentLivestreamBookings as $b)
                    <div class="tbl-row bk-grid">
                        <div class="user-pair">
                            <div class="mini-avatar">{{ strtoupper(substr($b->user->name ?? 'U', 0, 1)) }}</div>
                            <span class="cell-primary" style="font-size:11.5px">{{ $b->user->name ?? 'N/A' }}</span>
                        </div>
                        <span class="cell-primary" style="font-size:11.5px">{{ $b->livestream->title ?? '-' }}</span>
                        <span class="cell-dim">—</span>
                    </div>
                @endforeach
                <div style="padding:8px 16px; border-top:1px solid var(--ref-border-subtle)">
                    <span style="font-size:10.5px; color:var(--ref-text-muted)">{{ $recentLivestreamBookings->count() }} booking(s) total</span>
                </div>
            @else
                <div class="empty-block">
                    <div class="empty-ico">
                        <i data-lucide="radio"></i>
                    </div>
                    <div class="empty-title">No livestream bookings</div>
                    <div class="empty-sub">Viewer bookings will appear here.</div>
                </div>
            @endif
        </div>
    </div>

<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
@endsection
