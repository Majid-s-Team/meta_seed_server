@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
<div class="mb-6 animate-fade-in">
    <h1 class="admin-page-title">Users</h1>
    <p class="admin-page-desc">Manage users and access</p>
</div>

@if(session('success'))
    <div class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email..." class="admin-input w-64">
    <select name="role" class="admin-input w-auto min-w-[120px]">
        <option value="">All roles</option>
        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
    </select>
    <button type="submit" class="admin-btn-ghost">Search</button>
</form>

<div class="admin-card overflow-hidden">
    <table class="admin-table w-full">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll" class="rounded border-white/20 bg-white/5 text-[#6A5CFF]" title="Select all"></th>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Balance</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $u->id }}" class="row-select rounded border-white/20 bg-white/5 text-[#6A5CFF]"></td>
                    <td class="text-[var(--meta-text-muted)]">{{ $u->id }}</td>
                    <td class="font-medium text-white">{{ $u->name }}</td>
                    <td class="text-[var(--meta-text-secondary)]">{{ $u->email }}</td>
                    <td>
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium {{ $u->role === 'admin' ? 'bg-[var(--meta-accent-start)]/20 text-[var(--meta-accent-end)]' : 'bg-white/10 text-[var(--meta-text-secondary)]' }}">{{ $u->role }}</span>
                    </td>
                    <td>{{ $u->wallet->balance ?? 0 }}</td>
                    <td>
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium {{ $u->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.users.show', $u) }}" class="text-[var(--meta-accent-end)] hover:underline font-medium text-sm">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        @include('admin.partials.empty', ['icon' => 'users', 'title' => 'No users found', 'description' => 'Try adjusting your search or filters.'])
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if($users->hasPages())
        <div class="px-5 py-4 border-t border-[var(--meta-border)]">{{ $users->links('admin.partials.pagination') }}</div>
    @endif
</div>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-select').forEach(cb => { cb.checked = this.checked; });
});
</script>
@endsection
