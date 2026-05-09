<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermissionTo('notifications.view'), 403);

        $notifications = $request->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, DatabaseNotification $notification)
    {
        abort_unless($request->user()->id === $notification->notifiable_id, 403);

        $notification->markAsRead();

        return back()->with('success', __('Notification marked as read.'));
    }

    public function open(Request $request, DatabaseNotification $notification)
    {
        abort_unless($request->user()->id === $notification->notifiable_id, 403);

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        $targetUrl = $notification->data['url'] ?? null;

        if (is_string($targetUrl) && $targetUrl !== '') {
            return redirect()->to($targetUrl);
        }

        return redirect()
            ->route('notifications.index')
            ->with('info', __('This notification does not have a linked page.'));
    }

    public function markAllAsRead(Request $request)
    {
        abort_unless($request->user()->hasPermissionTo('notifications.view'), 403);

        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', __('All notifications were marked as read.'));
    }
}
