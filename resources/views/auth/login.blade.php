@php
    $isRtl = app()->isLocale('ar');
    $loginBadge = $isRtl ? 'تسجيل الدخول' : 'Sign in';
    $loginTitle = $isRtl ? 'أهلًا بعودتك' : 'Welcome back';
    $loginSubtitle = $isRtl
        ? 'ادخل إلى مساحة العمل لمتابعة العملاء، المشاريع، والمهام.'
        : 'Access your workspace to manage customers, projects, and tasks.';
@endphp

<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="space-y-6">
        <div class="space-y-3">
            <div class="inline-flex items-center rounded-full border border-cyan-400/20 bg-cyan-500/10 px-3 py-1 text-xs font-semibold text-cyan-300">
                {{ $loginBadge }}
            </div>
            <div class="space-y-2">
                <h2 class="text-2xl font-semibold text-white sm:text-[2rem]">{{ $loginTitle }}</h2>
                <p class="text-sm leading-7 text-slate-400">{{ $loginSubtitle }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div class="space-y-2">
                <x-input-label for="email" :value="__('Email')" class="text-sm font-medium text-slate-300" />
                <x-text-input
                    id="email"
                    class="block w-full"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div class="space-y-2">
                <x-input-label for="password" :value="__('Password')" class="text-sm font-medium text-slate-300" />
                <x-text-input
                    id="password"
                    class="block w-full"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div class="flex items-center justify-between gap-4">
                <label for="remember_me" class="inline-flex items-center gap-3 text-sm text-slate-400">
                    <input
                        id="remember_me"
                        type="checkbox"
                        class="h-4 w-4 rounded border-white/15 bg-white/5 text-cyan-500 focus:ring-cyan-500/40"
                        name="remember"
                    >
                    <span>{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-cyan-300 transition hover:text-cyan-200" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <button type="submit" class="btn-primary w-full justify-center py-3.5 text-sm font-semibold shadow-lg shadow-cyan-500/20">
                {{ __('Log in') }}
            </button>
        </form>

        @if (Route::has('register'))
            <div class="mt-8 border-t border-white/10 pt-8 text-center">
                <div class="text-sm text-slate-400">
                    {{ $isRtl ? 'هل تريد إنشاء حساب جديد؟' : 'Need a new account?' }}
                </div>
                <a href="{{ route('register') }}" class="btn-secondary mt-3 w-full justify-center py-3.5 text-sm font-semibold">
                    {{ $isRtl ? 'إنشاء حساب' : __('Create Account') }}
                </a>
            </div>
        @endif
    </div>
</x-guest-layout>
