@php
    $isRtl = app()->isLocale('ar');
    $languageSwitchLocale = $isRtl ? 'en' : 'ar';
    $languageSwitchLabel = $isRtl ? __('English') : 'العربية';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CRM & PM System</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700,800|instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.18),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(245,158,11,0.18),_transparent_25%),linear-gradient(180deg,#020617_0%,#0f172a_100%)] px-4 py-10">
            <div class="mx-auto max-w-7xl">
                <header class="flex items-center justify-between rounded-[2rem] border border-white/10 bg-white/5 px-6 py-5 backdrop-blur-xl">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-500/20 font-black text-cyan-300">CP</div>
                        <div>
                            <div class="text-xs uppercase tracking-[0.35em] text-cyan-300">{{ __('Workspace') }}</div>
                            <div class="text-2xl font-semibold text-white">CRM & PM System</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('locale.update', $languageSwitchLocale) }}" class="btn-secondary">{{ $languageSwitchLabel }}</a>
                        <a href="{{ route('login') }}" class="btn-secondary">{{ __('Login') }}</a>
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-primary">{{ __('Create Account') }}</a>
                        @endif
                    </div>
                </header>

                <section class="grid gap-8 pt-14 lg:grid-cols-[1.15fr_0.85fr]">
                    <div class="space-y-8">
                        <div class="inline-flex rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs uppercase tracking-[0.3em] text-cyan-300">{{ __('Monolithic Laravel Workspace') }}</div>
                        <h1 class="max-w-4xl text-5xl font-semibold leading-tight text-white sm:text-6xl">{{ __('From customer acquisition to project delivery, everything runs inside one elegant control center.') }}</h1>
                        <p class="max-w-2xl text-lg leading-8 text-slate-300">{{ __('Built with Blade, Laravel, and a SaaS-style dashboard experience, this workspace joins CRM, deals, tasks, Kanban, reporting, notifications, and audit history in one production-oriented flow.') }}</p>
                        <div class="flex flex-wrap gap-4">
                            <a href="{{ route('login') }}" class="btn-primary">{{ __('Open Workspace') }}</a>
                            @if(Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-secondary">{{ __('Register') }}</a>
                            @endif
                        </div>
                    </div>
                    <div class="grid gap-5">
                        <div class="panel"><div class="text-sm text-slate-400">{{ __('CRM') }}</div><div class="mt-2 text-2xl font-semibold text-white">{{ __('Customers, leads, deals, follow-ups, interactions') }}</div></div>
                        <div class="panel"><div class="text-sm text-slate-400">{{ __('Projects') }}</div><div class="mt-2 text-2xl font-semibold text-white">{{ __('Projects, tasks, Kanban, time tracking, team delivery') }}</div></div>
                        <div class="panel"><div class="text-sm text-slate-400">{{ __('Control') }}</div><div class="mt-2 text-2xl font-semibold text-white">{{ __('Permissions, notifications, reports, and audit log') }}</div></div>
                    </div>
                </section>
            </div>
        </div>
    </body>
</html>
