@extends('admin.layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="mb-6 animate-fade-in">
    <h1 class="admin-page-title">Transactions</h1>
    <p class="admin-page-desc">View-only transaction history</p>
</div>

<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <select name="type" class="admin-input w-auto min-w-[120px]">
        <option value="">All types</option>
        @foreach(\App\Models\Transaction::select('type')->distinct()->pluck('type') as $t)
            <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
        @endforeach
    </select>
    <input type="number" name="user_id" value="{{ request('user_id') }}" placeholder="User ID" class="admin-input w-28">
    <button type="submit" class="admin-btn-ghost">Filter</button>
</form>

<div class="admin-card overflow-hidden">
    <table class="admin-table w-full">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll" class="rounded border-white/20 bg-white/5 text-[#6A5CFF]" title="Select all"></th>
                <th>ID</th>
                <th>User</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $t->id }}" class="row-select rounded border-white/20 bg-white/5 text-[#6A5CFF]"></td>
                    <td class="text-[var(--meta-text-muted)]">{{ $t->id }}</td>
                    <td class="font-medium text-white">{{ $t->user->name ?? '-' }}</td>
                    <td class="text-[var(--meta-text-secondary)]">{{ $t->type }}</td>
                    <td>{{ $t->amount }}</td>
                    <td class="text-[var(--meta-text-secondary)]">{{ $t->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        @include('admin.partials.empty', ['icon' => 'wallet', 'title' => 'No transactions', 'description' => 'Transaction history will appear here.'])
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if($transactions->hasPages())
        <div class="px-5 py-4 border-t border-[var(--meta-border)]">{{ $transactions->links('admin.partials.pagination') }}</div>
    @endif
</div>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-select').forEach(cb => { cb.checked = this.checked; });
});
</script>
@endsection
