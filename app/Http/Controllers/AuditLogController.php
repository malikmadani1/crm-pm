<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $auditLogs = AuditLog::query()
            ->with(['user', 'auditable'])
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('module'), fn ($query) => $query->where('module', $request->string('module')))
            ->when($request->filled('event'), fn ($query) => $query->where('event', $request->string('event')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->string('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->string('to')))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $users = User::query()->orderBy('name')->get(['id', 'name']);
        $modules = AuditLog::query()->distinct()->orderBy('module')->pluck('module');
        $events = AuditLog::query()->distinct()->orderBy('event')->pluck('event');

        return view('audit-logs.index', compact('auditLogs', 'users', 'modules', 'events'));
    }

    public function show(AuditLog $auditLog)
    {
        $this->authorize('view', $auditLog);

        $auditLog->load(['user', 'auditable']);

        return view('audit-logs.show', compact('auditLog'));
    }
}
