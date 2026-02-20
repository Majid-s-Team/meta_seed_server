@extends('admin.layouts.app')

@section('title', 'User')

@section('content')
<div class="mb-6 animate-fade-in">
    <a href="{{ route('admin.users.index') }}" class="text-[var(--meta-text-secondary)] hover:text-white text-sm transition">← Users</a>
    <div class="flex justify-between items-start mt-2">
        <div>
            <h1 class="admin-page-title">{{ $user->name }}</h1>
            <p class="text-[var(--meta-text-secondary)]">{{ $user->email }}</p>
            <span class="inline-flex mt-2 px-2.5 py-1 rounded-lg text-xs font-medium {{ $user->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
        </div>
        @if($user->id !== auth()->id())
            <form action="{{ route('admin.users.toggle', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium transition {{ $user->is_active ? 'bg-red-500/20 text-red-400 hover:bg-red-500/30' : 'bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30' }}">
                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="admin-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Wallet</h2>
        <p class="text-2xl font-bold text-white">{{ $user->wallet->balance ?? 0 }} <span class="text-[var(--meta-text-secondary)] text-base font-normal">coins</span></p>
    </div>
    <div class="admin-card p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Bookings</h2>
        <p class="text-[var(--meta-text-secondary)] text-sm">Event: {{ $eventBookings->count() }} · Livestream: {{ $user->livestreamBookings->count() }}</p>
    </div>
</div>

<div class="mt-6 admin-card overflow-hidden">
    <h2 class="px-5 py-4 text-lg font-semibold text-white border-b border-[var(--meta-border)]">Recent Transactions</h2>
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
                    <td>{{ $t->amount }}</td>
                    <td class="text-[var(--meta-text-secondary)]">{{ $t->created_at?->format('Y-m-d H:i') }}</td>
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
        <h2 class="px-5 py-4 text-lg font-semibold text-white border-b border-[var(--meta-border)]">Event Bookings</h2>
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
        <h2 class="px-5 py-4 text-lg font-semibold text-white border-b border-[var(--meta-border)]">Livestream Bookings</h2>
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
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
@endsection
