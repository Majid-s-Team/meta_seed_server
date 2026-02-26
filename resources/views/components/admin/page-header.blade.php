{{-- Optional: @push('page_header_actions') before including to add buttons/actions --}}
<div class="flex justify-between items-start mb-8">
    <div>
        @if(!empty($eyebrow))
            <p class="section-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="admin-page-title {{ !empty($eyebrow) ? 'mt-1' : '' }}">{{ $title ?? '' }}</h1>
        @if(!empty($description))
            <p class="admin-page-desc">{{ $description }}</p>
        @endif
    </div>
    <div class="flex items-center gap-3">
        @stack('page_header_actions')
    </div>
</div>
