<?php

namespace App\Providers;

use App\Models\AttendanceRecord;
use App\Models\Role;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }

            return null;
        });

        View::composer('*', function ($view): void {
            $user = auth()->user();

            try {
                $unreadNotificationsCount = $user?->unreadNotifications()->count() ?? 0;
                $roleOptions = Schema::hasTable('roles')
                    ? Role::query()->orderBy('name')->get(['id', 'name', 'slug'])
                    : collect();
                $todayAttendance = $user && Schema::hasTable('attendance_records')
                    ? AttendanceRecord::query()
                        ->where('user_id', $user->id)
                        ->whereDate('work_date', now()->toDateString())
                        ->latest('checked_in_at')
                        ->first()
                    : null;
                $activeTaskTimer = $user && Schema::hasTable('time_entries')
                    ? TimeEntry::query()
                        ->with('task')
                        ->where('user_id', $user->id)
                        ->whereNull('ended_at')
                        ->latest('started_at')
                        ->first()
                    : null;
            } catch (Throwable $exception) {
                $unreadNotificationsCount = 0;
                $roleOptions = collect();
                $todayAttendance = null;
                $activeTaskTimer = null;
            }

            $view->with('appUnreadNotificationsCount', $unreadNotificationsCount);
            $view->with('appRoleOptions', $roleOptions);
            $view->with('appTodayAttendance', $todayAttendance);
            $view->with('appActiveTaskTimer', $activeTaskTimer);
        });
    }
}
