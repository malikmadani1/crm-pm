<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Notifications" description="All workflow alerts from tasks, projects, comments, and CRM conversions." />
    </x-slot>

    <div class="space-y-4">
        @forelse($notifications as $notification)
            @php($hasUrl = ! empty($notification->data['url']))
            <div class="panel flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                @if($hasUrl)
                    <a href="{{ route('notifications.open', $notification) }}" class="notification-card-link -m-2 block flex-1 rounded-[inherit] p-2">
                        <div>
                            <div class="text-lg font-semibold text-white">{{ __($notification->data['title'] ?? 'Notification') }}</div>
                            <div class="mt-1 text-sm text-slate-400">{{ __($notification->data['message'] ?? '') }}</div>
                            <div class="mt-3 text-xs text-slate-500">{{ $notification->created_at?->diffForHumans() }}</div>
                        </div>
                    </a>
                @else
                    <div class="flex-1">
                        <div class="text-lg font-semibold text-white">{{ __($notification->data['title'] ?? 'Notification') }}</div>
                        <div class="mt-1 text-sm text-slate-400">{{ __($notification->data['message'] ?? '') }}</div>
                        <div class="mt-3 text-xs text-slate-500">{{ $notification->created_at?->diffForHumans() }}</div>
                    </div>
                @endif

                <div class="flex items-center gap-3 lg:shrink-0">
                    @if(! $notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn-secondary">{{ __('Mark as read') }}</button>
                        </form>
                    @endif

                    @if($hasUrl)
                        <a href="{{ route('notifications.open', $notification) }}" class="btn-primary">{{ __('Open') }}</a>
                    @endif
                </div>
            </div>
        @empty
            <x-empty-state title="No notifications yet" message="Workflow events and assignments will appear here automatically." />
        @endforelse

        {{ $notifications->links() }}
    </div>
</x-app-layout>
