@props(['title' => null])

<div {{ $attributes->merge(['class' => 'admin-card']) }}>
    @if($title)
        <div class="card-header">
            <h3 class="card-header-title">{{ $title }}</h3>
            @isset($headerActions)
                <div class="card-header-actions">{{ $headerActions }}</div>
            @endisset
        </div>
    @endif
    {{ $slot }}
</div>
