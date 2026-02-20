@extends('admin.layouts.app')

@section('title', 'Livestream Bookings')

@section('content')
<div class="mb-6 animate-fade-in">
    <a href="{{ route('admin.bookings.index') }}" class="text-[var(--meta-text-secondary)] hover:text-white text-sm transition">← Bookings</a>
    <h1 class="admin-page-title mt-1">Livestream Bookings</h1>
</div>

<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <select name="livestream_id" class="admin-input w-auto min-w-[220px]">
        <option value="">All livestreams</option>
        @foreach($livestreams as $ls)
            <option value="{{ $ls->id }}" {{ request('livestream_id') == $ls->id ? 'selected' : '' }}>{{ $ls->title }} ({{ $ls->scheduled_at?->format('M d, H:i') }})</option>
        @endforeach
    </select>
    <button type="submit" class="admin-btn-ghost">Filter</button>
</form>

<div class="admin-card overflow-hidden">
    <table class="admin-table w-full">
        <thead>
            <tr>
                <th>User</th>
                <th>Livestream</th>
                <th>Scheduled</th>
                <th>Booked at</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $b)
                <tr>
                    <td class="font-medium text-white">{{ $b->user->name ?? '-' }}</td>
                    <td class="text-[var(--meta-text-secondary)]">{{ $b->livestream->title ?? '-' }}</td>
                    <td class="text-[var(--meta-text-secondary)]">{{ $b->livestream->scheduled_at?->format('M d, H:i') ?? '-' }}</td>
                    <td class="text-[var(--meta-text-secondary)]">{{ $b->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        @include('admin.partials.empty', ['icon' => 'radio', 'title' => 'No livestream bookings', 'description' => 'Viewer bookings will appear here.'])
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if($bookings->hasPages())
        <div class="px-5 py-4 border-t border-[var(--meta-border)]">{{ $bookings->links('admin.partials.pagination') }}</div>
    @endif
</div>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
@endsection
