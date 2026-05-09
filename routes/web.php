<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CRM\CustomerController;
use App\Http\Controllers\CRM\CustomerInteractionController;
use App\Http\Controllers\CRM\DealController;
use App\Http\Controllers\CRM\FollowUpController;
use App\Http\Controllers\CRM\LeadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PM\KanbanController;
use App\Http\Controllers\PM\ProjectController;
use App\Http\Controllers\PM\TaskCommentController;
use App\Http\Controllers\PM\TaskController;
use App\Http\Controllers\PM\TaskTimerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Team\RoleController;
use App\Http\Controllers\Team\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    Route::resource('customers', CustomerController::class);
    Route::post('/customer-interactions', [CustomerInteractionController::class, 'store'])->name('customer-interactions.store');
    Route::post('/follow-ups', [FollowUpController::class, 'store'])->name('follow-ups.store');
    Route::patch('/follow-ups/{followUp}', [FollowUpController::class, 'update'])->name('follow-ups.update');
    Route::delete('/follow-ups/{followUp}', [FollowUpController::class, 'destroy'])->name('follow-ups.destroy');

    Route::resource('leads', LeadController::class);
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');

    Route::get('/deals-pipeline', [DealController::class, 'pipeline'])->name('deals.pipeline');
    Route::patch('/deals/{deal}/stage', [DealController::class, 'updateStage'])->name('deals.update-stage');
    Route::resource('deals', DealController::class);

    Route::resource('projects', ProjectController::class);
    Route::resource('tasks', TaskController::class);
    Route::post('/tasks/{task}/timer/start', [TaskTimerController::class, 'start'])->name('tasks.timer.start');
    Route::post('/tasks/{task}/timer/stop', [TaskTimerController::class, 'stop'])->name('tasks.timer.stop');
    Route::get('/kanban', [KanbanController::class, 'index'])->name('kanban.index');
    Route::patch('/kanban/{task}/move', [KanbanController::class, 'move'])->name('kanban.move');
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
    Route::delete('/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('tasks.comments.destroy');

    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/crm', [ReportController::class, 'crm'])->name('reports.crm');
    Route::get('/reports/projects', [ReportController::class, 'projects'])->name('reports.projects');
    Route::get('/reports/tasks', [ReportController::class, 'tasks'])->name('reports.tasks');
    Route::get('/reports/team', [ReportController::class, 'team'])->name('reports.team');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/reports/export/{report}', [ReportController::class, 'export'])->name('reports.export');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
});

require __DIR__.'/auth.php';
