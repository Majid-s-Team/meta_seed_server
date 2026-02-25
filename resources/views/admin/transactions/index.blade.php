@extends('admin.layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="animate-fade-in">
    {{-- Page header (Section 13/20 + 23) --}}
    <div class="flex justify-between items-start mb-8">
        <div>
            <p class="section-eyebrow">Transactions</p>
            <h1 class="admin-page-title mt-1">Transactions</h1>
            <p class="admin-page-desc">View-only transaction history</p>
        </div>
        <div class="flex items-center gap-3"></div>
    </div>

    {{-- Filters (Section 11: form-group, form-label, admin-input) --}}
    <form method="GET" class="flex flex-wrap items-end gap-4 mb-5">
        <div class="form-group">
            <label for="type" class="form-label">Type</label>
            <select name="type" id="type" class="admin-input w-auto min-w-[120px]">
                <option value="">All types</option>
                @foreach(\App\Models\Transaction::select('type')->distinct()->pluck('type') as $t)
                    <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="user_id" class="form-label">User ID</label>
            <input type="number" name="user_id" id="user_id" value="{{ request('user_id') }}" placeholder="User ID" class="admin-input w-28">
        </div>
        <div class="form-group">
            <button type="submit" class="admin-btn-ghost">Filter</button>
        </div>
    </form>

    {{-- Table card (Section 9 + 23: card-header, admin-table, td-secondary) --}}
    <div class="admin-card overflow-hidden section-gap">
        <div class="card-header">
            <h3 class="card-header-title">Transaction list</h3>
            <div class="card-header-actions"></div>
        </div>
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
                        <td class="td-secondary">{{ $t->id }}</td>
                        <td class="font-medium text-white">{{ $t->user->name ?? '-' }}</td>
                        <td class="td-secondary">{{ $t->type }}</td>
                        <td class="td-secondary">{{ $t->amount }}</td>
                        <td class="td-secondary">{{ $t->created_at?->format('Y-m-d H:i') }}</td>
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
</div>
<script>
if (typeof lucide !== 'undefined') lucide.createIcons();
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-select').forEach(cb => { cb.checked = this.checked; });
});
</script>
@endsection
