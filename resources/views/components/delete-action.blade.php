@props([
    'action',
    'title' => __('Confirm deletion'),
    'message' => __('This action permanently removes the selected record from the system.'),
    'buttonLabel' => __('Delete'),
])

<div x-data="{ open: false }" class="inline-flex">
    <button
        type="button"
        class="delete-action-trigger"
        @click="open = true"
    >
        {{ $buttonLabel }}
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.opacity
        x-on:keydown.escape.window="open = false"
        class="fixed inset-0 z-[250] flex items-center justify-center p-4"
    >
        <div class="delete-action-backdrop absolute inset-0 backdrop-blur-sm" @click="open = false"></div>

        <div
            x-show="open"
            x-transition:enter="transform ease-out duration-200"
            x-transition:enter-start="translate-y-3 opacity-0 scale-95"
            x-transition:enter-end="translate-y-0 opacity-100 scale-100"
            x-transition:leave="transform ease-in duration-150"
            x-transition:leave-start="translate-y-0 opacity-100 scale-100"
            x-transition:leave-end="translate-y-2 opacity-0 scale-95"
            class="delete-action-modal relative w-full max-w-md overflow-hidden shadow-2xl"
        >
            <div class="flex items-start gap-4 p-6">
                <div class="delete-action-icon flex h-12 w-12 shrink-0 items-center justify-center text-lg font-black">
                    !
                </div>

                <div class="min-w-0 flex-1">
                    <h3 class="delete-action-title text-lg font-semibold">{{ $title }}</h3>
                    <p class="delete-action-message mt-2 text-sm leading-7">{{ $message }}</p>
                </div>

                <button
                    type="button"
                    class="delete-action-close rounded-xl px-2 py-1 transition"
                    @click="open = false"
                    aria-label="{{ __('Close') }}"
                >
                    &times;
                </button>
            </div>

            <div class="delete-action-footer flex items-center justify-end gap-3 border-t px-6 py-4">
                <button type="button" class="btn-secondary" @click="open = false">{{ __('Cancel') }}</button>

                <form method="POST" action="{{ $action }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="delete-action-confirm">
                        {{ $buttonLabel }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
