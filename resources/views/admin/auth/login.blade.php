<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login — MetaSeat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
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
        }
        body { font-family: 'Inter', sans-serif; background: var(--meta-bg); color: var(--meta-text); }
        .admin-input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--meta-border);
            color: var(--meta-text);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .admin-input:focus {
            outline: none;
            border-color: var(--meta-accent-start);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
        }
        .admin-card {
            background: var(--meta-card);
            border: 1px solid var(--meta-border);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.2);
        }
        .admin-btn-primary {
            width: 100%;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            background: linear-gradient(135deg, var(--meta-accent-start) 0%, var(--meta-accent-end) 100%);
            color: #fff;
            box-shadow: 0 4px 20px rgba(108, 92, 231, 0.35);
            transition: all 0.2s ease;
        }
        .admin-btn-primary:hover {
            box-shadow: 0 6px 28px rgba(108, 92, 231, 0.45);
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md animate-fade-in" style="animation: fadeIn 0.4s ease;">
        <div class="admin-card p-8">
            <h1 class="text-2xl font-bold text-white tracking-tight">MetaSeat Admin</h1>
            <p class="text-[var(--meta-text-secondary)] text-sm mt-1 mb-6">Sign in to manage the platform</p>

            @if ($errors->any())
                <div class="mb-5 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-[var(--meta-text-secondary)] mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           class="admin-input placeholder-[var(--meta-text-muted)]">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-[var(--meta-text-secondary)] mb-2">Password</label>
                    <input type="password" name="password" id="password" required class="admin-input">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember" class="rounded border-white/20 bg-white/5 text-[var(--meta-accent-start)] focus:ring-[var(--meta-accent-start)]">
                    <label for="remember" class="text-sm text-[var(--meta-text-secondary)]">Remember me</label>
                </div>
                <button type="submit" class="admin-btn-primary">
                    Sign in
                </button>
            </form>
        </div>
        <p class="text-center text-[var(--meta-text-muted)] text-xs mt-5">Use admin credentials from your .env or seed.</p>
    </div>
    <style>@keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }</style>
    <script>lucide.createIcons();</script>
</body>
</html>
