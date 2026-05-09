@php
    $isRtl = app()->isLocale('ar');
    $languageSwitchLocale = $isRtl ? 'en' : 'ar';
    $languageSwitchFlagClass = $languageSwitchLocale === 'en' ? 'fi-gb' : 'fi-sy';
    $languageSwitchCode = strtoupper($languageSwitchLocale);
    $languageSwitchTitle = $isRtl ? 'English' : 'العربية';
    $guestEyebrow = $isRtl ? 'مساحة عمل موحدة' : 'Unified workspace';
    $guestTitle = $isRtl
        ? 'إدارة العملاء والمشاريع من شاشة واحدة.'
        : 'Manage customers and projects from one calm workspace.';
    $guestSubtitle = $isRtl
        ? 'واجهة عملية ومختصرة لمتابعة العملاء، الصفقات، المشاريع، والمهام اليومية بدون تشتيت.'
        : 'A focused workspace for CRM, deals, projects, and daily execution without the noise.';
    $guestHighlights = $isRtl
        ? ['العملاء والصفقات', 'المشاريع والمهام', 'لوحة تنفيذ واضحة']
        : ['Customers and deals', 'Projects and tasks', 'Clear execution flow'];
    $themeLightLabel = $isRtl ? 'الوضع الفاتح' : 'Light mode';
    $themeDarkLabel = $isRtl ? 'الوضع الداكن' : 'Dark mode';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'CRM & PM System') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
        <script>
            (() => {
                const storedTheme = localStorage.getItem('theme');
                const theme = storedTheme === 'light' || storedTheme === 'dark'
                    ? storedTheme
                    : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

                document.documentElement.classList.remove('light', 'dark');
                document.documentElement.classList.add(theme);
                document.documentElement.style.colorScheme = theme;
            })();
        </script>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cairo:400,500,600,700,800|instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="min-h-screen bg-slate-950 text-slate-100"
        x-data="{
            theme: document.documentElement.classList.contains('light') ? 'light' : 'dark',
            applyTheme(value) {
                document.documentElement.classList.remove('light', 'dark');
                document.documentElement.classList.add(value);
                document.documentElement.style.colorScheme = value;
                localStorage.setItem('theme', value);
            },
        }"
        x-init="applyTheme(theme); $watch('theme', value => applyTheme(value));"
    >
        <x-toast-stack />

        <div class="guest-shell min-h-screen px-4 py-10">
            <div class="guest-frame mx-auto grid min-h-[calc(100vh-5rem)] max-w-6xl overflow-hidden rounded-[2rem] border border-white/10 bg-slate-900/70 shadow-2xl shadow-slate-950/50 backdrop-blur-xl lg:grid-cols-[1.05fr_0.95fr]">
                <div class="guest-hero hidden flex-col justify-between bg-gradient-to-br from-cyan-500/18 via-slate-900/70 to-amber-500/10 p-10 lg:flex xl:p-12">
                    <div class="max-w-xl">
                        <div class="guest-copy-badge inline-flex items-center rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[11px] font-semibold tracking-[0.28em] text-cyan-300">{{ $guestEyebrow }}</div>
                        <h1 class="mt-8 text-4xl font-semibold leading-[1.25] text-white xl:text-[2.9rem]">{{ $guestTitle }}</h1>
                        <p class="mt-5 max-w-lg text-sm leading-8 text-slate-300 xl:text-base">{{ $guestSubtitle }}</p>
                    </div>

                    <div class="guest-highlights grid gap-3">
                        @foreach ($guestHighlights as $highlight)
                            <div class="guest-highlight-item flex items-center justify-between rounded-3xl border border-white/10 bg-white/5 px-5 py-4 text-sm text-slate-200">
                                <span>{{ $highlight }}</span>
                                <span class="h-2.5 w-2.5 rounded-full bg-cyan-400/90"></span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-center p-6 sm:p-10 lg:p-12">
                    <div class="w-full max-w-md">
                        <div class="mb-8 flex items-start justify-end gap-4">
                            <div class="guest-toolbar flex items-center gap-2">
                                <button
                                    type="button"
                                    class="header-control guest-toolbar-button"
                                    @click="theme = theme === 'dark' ? 'light' : 'dark'"
                                    :title="theme === 'dark' ? '{{ $themeLightLabel }}' : '{{ $themeDarkLabel }}'"
                                    :aria-label="theme === 'dark' ? '{{ $themeLightLabel }}' : '{{ $themeDarkLabel }}'"
                                >
                                    <span class="header-control-icon" x-show="theme === 'dark'" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.2M12 18.8V21M5.64 5.64l1.56 1.56M16.8 16.8l1.56 1.56M3 12h2.2M18.8 12H21M5.64 18.36 7.2 16.8M16.8 7.2l1.56-1.56" />
                                            <circle cx="12" cy="12" r="4.2" />
                                        </svg>
                                    </span>
                                    <span class="header-control-icon" x-show="theme !== 'dark'" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.2 15.1A8.7 8.7 0 0 1 8.9 3.8a9 9 0 1 0 11.3 11.3Z" />
                                        </svg>
                                    </span>
                                </button>

                                <a href="{{ route('locale.update', $languageSwitchLocale) }}" class="header-control guest-locale-button" title="{{ $languageSwitchTitle }}" aria-label="{{ $languageSwitchTitle }}">
                                    <span class="header-control-flag" aria-hidden="true">
                                        <span class="fi {{ $languageSwitchFlagClass }}"></span>
                                    </span>
                                    <span class="header-control-code">{{ $languageSwitchCode }}</span>
                                </a>
                            </div>
                        </div>

                        <div class="guest-card rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-xl backdrop-blur-sm sm:p-8">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
