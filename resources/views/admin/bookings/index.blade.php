@extends('admin.layouts.app')

@section('title', 'Bookings')

@section('content')
<div class="animate-fade-in">
    {{-- Page header (Section 13/20 + 23) --}}
    <div class="flex justify-between items-start mb-8">
        <div>
            <p class="section-eyebrow">Bookings</p>
            <h1 class="admin-page-title mt-1">Bookings</h1>
            <p class="admin-page-desc">Event and livestream bookings</p>
        </div>
        <div class="flex items-center gap-3"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-2xl">
    <a href="{{ route('admin.bookings.event') }}" class="admin-card block p-6 transition-all duration-200 hover:border-[var(--meta-accent-start)]/30 group">
        <div class="flex items-start gap-4">
            <div class="admin-stat-icon bg-[var(--meta-accent-start)]/20 text-[var(--meta-accent-end)] group-hover:bg-[var(--meta-accent-start)]/30 transition">
                <i data-lucide="calendar-days"></i>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-white mb-1">Event Bookings</h2>
                <p class="text-[var(--meta-text-secondary)] text-sm">View and export event attendee lists</p>
            </div>
        </div>
    </a>
    <a href="{{ route('admin.bookings.livestream') }}" class="admin-card block p-6 transition-all duration-200 hover:border-[var(--meta-accent-start)]/30 group">
        <div class="flex items-start gap-4">
            <div class="admin-stat-icon bg-blue-500/20 text-blue-400 group-hover:bg-blue-500/30 transition">
                <i data-lucide="radio"></i>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-white mb-1">Livestream Bookings</h2>
                <p class="text-[var(--meta-text-secondary)] text-sm">View livestream participants</p>
            </div>
        </div>
    </a>
    </div>
</div>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
@endsection
