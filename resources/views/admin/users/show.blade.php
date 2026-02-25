@extends('admin.layouts.app')

@section('title', 'User')

@section('content')
<div class="animate-fade-in">
    {{-- Page header (Section 13/20 + 23) --}}
    <div class="flex justify-between items-start mb-8">
        <div>
            <a href="{{ route('admin.users.index') }}" class="section-eyebrow text-[var(--meta-text-secondary)] hover:text-white transition block">← Users</a>
            <h1 class="admin-page-title mt-1">{{ $user->name }}</h1>
            <p class="admin-page-desc">{{ $user->email }}</p>
            @if($user->is_active)
                <span class="badge badge-active mt-2 inline-block">Active</span>
            @else
                <span class="badge badge-error mt-2 inline-block">Inactive</span>
            @endif
        </div>
        @if($user->id !== auth()->id())
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.users.toggle', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium transition {{ $user->is_active ? 'bg-red-500/20 text-red-400 hover:bg-red-500/30' : 'bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30' }}">
                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="admin-card stat-card p-6">
            <p class="stat-card-label">Wallet</p>
            <p class="stat-card-value">{{ $user->wallet->balance ?? 0 }} <span class="text-[var(--meta-text-secondary)] text-base font-normal">coins</span></p>
        </div>
        <div class="admin-card p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Bookings</h2>
            <p class="text-[var(--meta-text-secondary)] text-sm">Event: {{ $eventBookings->count() }} · Livestream: {{ $user->livestreamBookings->count() }}</p>
        </div>
    </div>

    {{-- Recent Transactions (Section 9 + 23: card-header, admin-table, td-secondary) --}}
    <div class="mt-6 admin-card overflow-hidden section-gap">
        <div class="card-header">
            <h3 class="card-header-title">Recent Transactions</h3>
            <div class="card-header-actions"></div>
        </div>
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions->take(20) as $t)
                    <tr>
                        <td class="font-medium text-white">{{ $t->type }}</td>
                        <td class="td-secondary">{{ $t->amount }}</td>
                        <td class="td-secondary">{{ $t->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            @include('admin.partials.empty', ['icon' => 'wallet', 'title' => 'No transactions', 'description' => 'Transaction history will appear here.'])
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="admin-card overflow-hidden">
            <div class="card-header">
                <h3 class="card-header-title">Event Bookings</h3>
                <div class="card-header-actions"></div>
            </div>
            <div class="divide-y divide-[var(--meta-border)] max-h-64 overflow-y-auto">
                @forelse($eventBookings->take(10) as $b)
                    <div class="px-5 py-3 text-sm hover:bg-[var(--meta-card-hover)] transition">
                        <span class="text-white font-medium">{{ $b->event->title ?? '-' }}</span>
                        <span class="text-[var(--meta-text-secondary)]"> · {{ $b->event->date ?? '' }}</span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-[var(--meta-text-secondary)] text-sm">No event bookings.</div>
                @endforelse
            </div>
        </div>
        <div class="admin-card overflow-hidden">
            <div class="card-header">
                <h3 class="card-header-title">Livestream Bookings</h3>
                <div class="card-header-actions"></div>
            </div>
            <div class="divide-y divide-[var(--meta-border)] max-h-64 overflow-y-auto">
                @forelse($user->livestreamBookings->take(10) as $b)
                    <div class="px-5 py-3 text-sm hover:bg-[var(--meta-card-hover)] transition">
                        <span class="text-white font-medium">{{ $b->livestream->title ?? '-' }}</span>
                        <span class="text-[var(--meta-text-secondary)]"> · {{ $b->livestream->scheduled_at?->format('M d') ?? '' }}</span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-[var(--meta-text-secondary)] text-sm">No livestream bookings.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
@endsection
