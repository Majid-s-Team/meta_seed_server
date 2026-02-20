<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview — {{ $page->title ?? 'CMS' }}</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 720px; margin: 2rem auto; padding: 0 1rem; background: #0B0B0F; color: #fff; }
        a { color: #6A5CFF; }
        .back { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="back"><a href="{{ route('admin.cms.index') }}">← Back to CMS</a></div>
    <h1>{{ $page->title ?? 'Untitled' }}</h1>
    <div class="content">
        {!! $page->content ?? '' !!}
    </div>
</body>
</html>
