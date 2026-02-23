<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Livestream Test') — MetaSeat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --meta-bg: #0B0F1A;
            --meta-card: #141A2E;
            --meta-border: rgba(255,255,255,0.06);
            --meta-accent-start: #6C5CE7;
            --meta-accent-end: #8E7CFF;
            --meta-text: #FFFFFF;
            --meta-text-secondary: #9CA3AF;
            --meta-text-muted: #6B7280;
            --meta-live: #EF4444;
        }
        body { font-family: 'Inter', sans-serif; background: var(--meta-bg); color: var(--meta-text); min-height: 100vh; }
        .test-card {
            background: var(--meta-card);
            border: 1px solid var(--meta-border);
            border-radius: 16px;
            padding: 1.5rem;
        }
        .test-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 500;
            background: linear-gradient(135deg, var(--meta-accent-start) 0%, var(--meta-accent-end) 100%);
            color: #fff;
            transition: all 0.2s ease;
        }
        .test-btn-primary:hover { opacity: 0.95; transform: translateY(-1px); }
        .test-btn-ghost {
            padding: 8px 16px;
            border-radius: 10px;
            background: rgba(255,255,255,0.06);
            color: var(--meta-text-secondary);
            transition: all 0.2s ease;
        }
        .test-btn-ghost:hover { background: rgba(255,255,255,0.1); color: var(--meta-text); }
        .test-btn-danger {
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 500;
            background: var(--meta-live);
            color: #fff;
            transition: all 0.2s ease;
        }
        .test-btn-danger:hover { opacity: 0.9; }
        .test-input {
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--meta-border);
            color: var(--meta-text);
        }
        .test-input::placeholder { color: var(--meta-text-muted); }
        .test-input:focus {
            outline: none;
            border-color: var(--meta-accent-start);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
        }
        .test-page-title { font-size: 1.5rem; font-weight: 700; color: var(--meta-text); }
        .test-page-desc { font-size: 0.875rem; color: var(--meta-text-secondary); margin-top: 4px; }
    </style>
    @stack('styles')
</head>
<body class="p-4 md:p-6">
    <div class="max-w-3xl mx-auto space-y-6">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
