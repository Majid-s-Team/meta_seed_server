@extends('admin.layouts.app')

@section('title', 'Users')
@section('breadcrumb_page', 'Users')

@section('content')
<div class="animate-fade-in">
    <div class="page-header">
        <div>
            <p class="page-eyebrow">Users</p>
            <h1 class="page-title">Users</h1>
            <p class="admin-page-desc">Manage users and access</p>
        </div>
        <div class="flex items-center gap-3"></div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif

    <form method="GET" class="filter-bar">
        <div class="form-group">
            <label for="search" class="form-label">Search</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name or email..." class="admin-input w-64">
        </div>
        <div class="form-group">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="admin-input w-auto min-w-[120px]">
                <option value="">All roles</option>
                <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="admin-btn-ghost">Search</button>
        </div>
    </form>

    {{-- Table card (Section 9 + 12 + 23: card-header, admin-table, td-secondary, badge) --}}
    <div class="admin-card overflow-hidden section-gap">
        <div class="card-header">
            <h3 class="card-header-title">User list</h3>
            <div class="card-header-actions"></div>
        </div>
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
                        <td class="td-secondary">{{ $u->id }}</td>
                        <td class="font-medium text-white">{{ $u->name }}</td>
                        <td class="td-secondary">{{ $u->email }}</td>
                        <td>
                            @if($u->role === 'admin')
                                <span class="badge badge-purple">{{ $u->role }}</span>
                            @else
                                <span class="badge badge-inactive">{{ $u->role }}</span>
                            @endif
                        </td>
                        <td class="td-secondary">{{ $u->wallet->balance ?? 0 }}</td>
                        <td>
                            @if($u->is_active)
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-error">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $u) }}" class="action-edit">View</a>
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
</div>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-select').forEach(cb => { cb.checked = this.checked; });
});
</script>
@endsection
