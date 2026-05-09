@props(['title', 'message', 'action' => null, 'href' => null])

<div class="rounded-[2rem] border border-dashed border-white/10 bg-white/5 px-6 py-12 text-center">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-cyan-500/10 text-2xl text-cyan-300">+</div>
    <h3 class="mt-5 text-xl font-semibold text-white">{{ __($title) }}</h3>
    <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-400">{{ __($message) }}</p>
    @if ($action && $href)
        <a href="{{ $href }}" class="mt-6 inline-flex rounded-2xl bg-cyan-500 px-5 py-3 text-sm font-semibold text-slate-950 hover:bg-cyan-400">{{ __($action) }}</a>
    @endif
</div>
