<div class="admin-empty animate-fade-in">
    @if(isset($icon))
        <i data-lucide="{{ $icon }}" class="w-12 h-12"></i>
    @else
        <i data-lucide="inbox" class="w-12 h-12"></i>
    @endif
    <p class="admin-empty-title">{{ $title ?? 'No data yet' }}</p>
    @if(isset($description))
        <p class="admin-empty-desc">{{ $description }}</p>
    @endif
</div>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
