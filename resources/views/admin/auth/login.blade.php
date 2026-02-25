<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login — MetaSeat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/admin-modern.css'])
    <style>@keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } } .animate-fade-in { animation: fadeIn 0.5s ease forwards; }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 surface-main" style="
    background-image:
        radial-gradient(ellipse 80% 60% at 50% -10%, rgba(108,92,231,0.18), transparent),
        radial-gradient(ellipse 40% 40% at 80% 80%, rgba(142,124,255,0.08), transparent);
    background-attachment: fixed;
">
    {{-- Decorative orbs --}}
    <div class="fixed -top-[200px] -left-[100px] w-[600px] h-[600px] rounded-full pointer-events-none" style="background:radial-gradient(circle,rgba(108,92,231,0.08),transparent 70%);"></div>
    <div class="fixed -bottom-[200px] -right-[100px] w-[500px] h-[500px] rounded-full pointer-events-none" style="background:radial-gradient(circle,rgba(142,124,255,0.06),transparent 70%);"></div>

    <div class="w-full max-w-[420px] relative z-10 animate-fade-in">
        {{-- Logo mark --}}
        <div class="text-center mb-8">
            <div class="w-[52px] h-[52px] rounded-2xl inline-flex items-center justify-center mb-3.5" style="background:var(--grad-brand);box-shadow:var(--glow-md);">
                <i data-lucide="zap" class="w-6 h-6 text-white"></i>
            </div>
            <h1 class="text-[1.375rem] font-bold text-white tracking-tight mb-1" style="letter-spacing:-0.03em;">Welcome back</h1>
            <p class="text-sm" style="color:var(--meta-text-secondary);">Sign in to MetaSeat Admin</p>
        </div>

        {{-- Card --}}
        <div class="admin-card relative overflow-hidden" style="padding:2rem;border-color:rgba(142,124,255,0.12);">
            {{-- Top accent line on card --}}
            <div class="absolute top-0 left-0 right-0 h-0.5 opacity-70" style="background:var(--grad-brand);border-radius:var(--r-md) var(--r-md) 0 0;"></div>

            @if ($errors->any())
                <div class="alert alert-error mb-5">
                    <i data-lucide="alert-circle"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email address</label>
                    <input type="email" name="email" id="email" class="admin-input placeholder-[var(--meta-text-muted)]" value="{{ old('email') }}" placeholder="admin@example.com" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" name="password" id="password" class="admin-input" placeholder="••••••••" required>
                </div>

                <div class="flex items-center gap-2 mb-5">
                    <input type="checkbox" name="remember" id="remember" class="rounded border-white/20 bg-white/5 text-[var(--meta-accent-start)] focus:ring-[var(--meta-accent-start)]">
                    <label for="remember" class="text-sm" style="color:var(--meta-text-secondary);">Remember me</label>
                </div>

                <button type="submit" class="admin-btn-primary w-full justify-center">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center mt-6 text-[0.75rem]" style="color:var(--meta-text-muted);">MetaSeat Admin Panel &mdash; Restricted Access</p>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
