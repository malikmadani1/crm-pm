<x-guest-layout>
    <div class="mx-auto max-w-2xl py-20 text-center">
        <div class="text-sm uppercase tracking-[0.35em] text-cyan-300">404</div>
        <h1 class="mt-4 text-4xl font-semibold text-white">{{ __('Page not found') }}</h1>
        <p class="mt-4 text-slate-400">{{ __('The page you are looking for could not be found.') }}</p>
        <a href="{{ route('dashboard') }}" class="btn-primary mt-6">{{ __('Back to Dashboard') }}</a>
    </div>
</x-guest-layout>
