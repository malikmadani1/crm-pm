@php
    $isRtl = app()->isLocale('ar');
    $errorBag = session('errors') instanceof \Illuminate\Support\ViewErrorBag
        ? session('errors')
        : new \Illuminate\Support\ViewErrorBag();

    $statusMessages = [
        'signed-in' => __('Signed in successfully.'),
        'signed-out' => __('Signed out successfully.'),
        'registered' => __('Account created successfully.'),
        'email-verified' => __('Email verified successfully.'),
        'password-confirmed' => __('Identity confirmed successfully.'),
        'verification-link-sent' => __('A new verification link has been sent to your email address.'),
        'password-updated' => __('Password updated successfully.'),
        'profile-updated' => __('Profile updated successfully.'),
    ];

    $toasts = collect();

    foreach (['success', 'error', 'warning', 'info'] as $level) {
        if (session()->has($level)) {
            $toasts->push([
                'type' => $level,
                'message' => session($level),
            ]);
        }
    }

    if (session()->has('status')) {
        $status = session('status');

        $toasts->push([
            'type' => 'success',
            'message' => $statusMessages[$status] ?? __($status),
        ]);
    }

    if ($errorBag->any()) {
        $toasts->push([
            'type' => 'error',
            'message' => $errorBag->count() > 1
                ? __('Please review the form. :count issues need attention.', ['count' => $errorBag->count()])
                : ($errorBag->first() ?: __('Please review the form.')),
        ]);
    }

    $initialToasts = $toasts->values()->all();
    $toneClasses = [
        'success' => 'toast-success',
        'error' => 'toast-error',
        'warning' => 'toast-warning',
        'info' => 'toast-info',
    ];
    $iconMap = [
        'success' => '✓',
        'error' => '!',
        'warning' => '•',
        'info' => 'i',
    ];
@endphp

<div
    x-data="{
        toasts: [],
        toneClasses: @js($toneClasses),
        iconMap: @js($iconMap),
        init() {
            (@js($initialToasts) || []).forEach((toast) => this.push(toast));
        },
        push(toast) {
            if (!toast?.message) return;

            const entry = {
                id: `${Date.now()}-${Math.random().toString(16).slice(2)}`,
                type: toast.type ?? 'info',
                message: toast.message,
                title: toast.title ?? null,
                progress: 100,
                visible: true,
                duration: toast.duration ?? 5200,
            };

            this.toasts.push(entry);

            const startedAt = Date.now();
            const frame = () => {
                const current = this.toasts.find((item) => item.id === entry.id);
                if (!current || !current.visible) return;

                const elapsed = Date.now() - startedAt;
                current.progress = Math.max(0, 100 - ((elapsed / current.duration) * 100));

                if (elapsed < current.duration) {
                    requestAnimationFrame(frame);
                    return;
                }

                this.dismiss(entry.id);
            };

            requestAnimationFrame(frame);
            setTimeout(() => this.dismiss(entry.id), entry.duration);
        },
        dismiss(id) {
            const toast = this.toasts.find((item) => item.id === id);
            if (!toast) return;

            toast.visible = false;
            setTimeout(() => {
                this.toasts = this.toasts.filter((item) => item.id !== id);
            }, 220);
        },
        titleFor(toast) {
            if (toast.title) return toast.title;

            const labels = {
                success: @js(__('Success')),
                error: @js(__('Error')),
                warning: @js(__('Warning')),
                info: @js(__('Info')),
            };

            return labels[toast.type] ?? labels.info;
        },
    }"
    x-on:app-toast.window="push($event.detail)"
    class="fixed bottom-4 {{ $isRtl ? 'left-4 items-start' : 'right-4 items-end' }} z-[2147483647] flex w-auto flex-col gap-3"
    style="{{ $isRtl ? 'left: 1rem; right: auto;' : 'left: auto; right: 1rem;' }} bottom: 1rem;"
    aria-live="polite"
    aria-atomic="true"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transform ease-out duration-300"
            x-transition:enter-start="translate-y-3 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transform ease-in duration-200"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-2 opacity-0"
            class="toast-shell w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-[1.75rem] border backdrop-blur-xl"
            :class="toneClasses[toast.type] ?? toneClasses.info"
            role="status"
        >
            <div class="flex items-start gap-4 px-4 py-4 sm:px-5">
                <div class="toast-icon flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-black" x-text="iconMap[toast.type] ?? iconMap.info"></div>
                <div class="min-w-0 flex-1">
                    <div class="toast-title text-sm font-semibold" x-text="titleFor(toast)"></div>
                    <div class="toast-message mt-1 text-sm leading-6" x-text="toast.message"></div>
                </div>
                <button
                    type="button"
                    class="toast-close rounded-xl px-2 py-1 text-sm transition hover:bg-white/10"
                    @click="dismiss(toast.id)"
                    aria-label="{{ __('Close notification') }}"
                >
                    &times;
                </button>
            </div>
            <div class="toast-progress-track h-1.5 w-full">
                <div class="toast-progress-bar h-full" :style="`width: ${toast.progress}%`"></div>
            </div>
        </div>
    </template>
</div>
