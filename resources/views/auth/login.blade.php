@php
    $isRtl = app()->isLocale('ar');
    $loginBadge = __('Sign in');
    $loginTitle = __('Welcome back');
    $loginSubtitle = __('Access your workspace to manage customers, projects, and tasks.');
    $demoAccounts = [
        [
            'label' => __('Super Admin'),
            'roles' => __('All core roles'),
            'email' => 'admin@crm-pm.test',
            'password' => 'password',
        ],
        [
            'label' => __('System Admin 2'),
            'roles' => __('All core roles'),
            'email' => 'admin2@crm-pm.test',
            'password' => 'password',
        ],
        [
            'label' => __('System Admin 3'),
            'roles' => __('All core roles'),
            'email' => 'admin3@crm-pm.test',
            'password' => 'password',
        ],
    ];
@endphp

<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div
        class="space-y-6"
        x-data="{
            fillLogin(email, password) {
                $refs.email.value = email;
                $refs.password.value = password;
                $refs.email.dispatchEvent(new Event('input', { bubbles: true }));
                $refs.password.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }"
    >
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
                    x-ref="email"
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
                    x-ref="password"
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

        <div class="border-t border-white/10 pt-6">
            <div class="mb-3 space-y-1">
                <h3 class="text-sm font-semibold text-white">{{ __('Test login accounts') }}</h3>
                <p class="text-xs leading-6 text-slate-400">{{ __('Click an account to fill the login form.') }}</p>
            </div>

            <div class="space-y-2">
                @foreach ($demoAccounts as $account)
                    <button
                        type="button"
                        class="w-full rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 {{ $isRtl ? 'text-right' : 'text-left' }} transition hover:border-cyan-400/40 hover:bg-white/[0.07] focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                        x-on:click="fillLogin(@js($account['email']), @js($account['password']))"
                    >
                        <span class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-sm font-semibold text-white">{{ $account['label'] }}</span>
                            <span class="rounded-full border border-cyan-400/20 bg-cyan-500/10 px-2.5 py-1 text-[11px] font-semibold text-cyan-300">{{ $account['roles'] }}</span>
                        </span>
                        <span class="mt-2 grid gap-1 text-xs leading-5 text-slate-400">
                            <span>
                                {{ __('Email') }}:
                                <span dir="ltr" class="font-mono text-slate-200">{{ $account['email'] }}</span>
                            </span>
                            <span>
                                {{ __('Password') }}:
                                <span dir="ltr" class="font-mono text-slate-200">{{ $account['password'] }}</span>
                            </span>
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        @if (Route::has('register'))
            <div class="mt-8 border-t border-white/10 pt-8 text-center">
                <div class="text-sm text-slate-400">
                    {{ __('Need a new account?') }}
                </div>
                <a href="{{ route('register') }}" class="btn-secondary mt-3 w-full justify-center py-3.5 text-sm font-semibold">
                    {{ __('Create Account') }}
                </a>
            </div>
        @endif
    </div>
</x-guest-layout>
