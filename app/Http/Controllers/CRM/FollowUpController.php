<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFollowUpRequest;
use App\Models\FollowUp;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function store(StoreFollowUpRequest $request, AuditLogService $auditLogService)
    {
        $followUp = FollowUp::query()->create($request->validated());

        $auditLogService->record(
            module: 'crm',
            event: 'follow_up_created',
            auditable: $followUp,
            newValues: $followUp->toArray(),
            description: __('Follow-up :title created.', ['title' => $followUp->title]),
        );

        return back()->with('success', __('Follow-up scheduled successfully.'));
    }

    public function update(Request $request, FollowUp $followUp, AuditLogService $auditLogService)
    {
        abort_unless(
            $request->user()->hasPermissionTo('customers.update') || $request->user()->hasPermissionTo('leads.update'),
            403
        );

        $oldValues = $followUp->toArray();

        $followUp->update([
            'status' => $request->boolean('completed') ? 'completed' : $request->string('status', $followUp->status),
            'completed_at' => $request->boolean('completed') ? now() : null,
        ]);

        $auditLogService->record(
            module: 'crm',
            event: 'follow_up_updated',
            auditable: $followUp,
            oldValues: $oldValues,
            newValues: $followUp->fresh()->toArray(),
            description: __('Follow-up :title updated.', ['title' => $followUp->title]),
        );

        return back()->with('success', __('Follow-up updated successfully.'));
    }

    public function destroy(Request $request, FollowUp $followUp, AuditLogService $auditLogService)
    {
        abort_unless(
            $request->user()->hasPermissionTo('customers.update') || $request->user()->hasPermissionTo('leads.update'),
            403
        );

        $auditLogService->record(
            module: 'crm',
            event: 'follow_up_deleted',
            auditable: $followUp,
            oldValues: $followUp->toArray(),
            description: __('Follow-up :title deleted.', ['title' => $followUp->title]),
        );

        $followUp->delete();

        return back()->with('success', __('Follow-up deleted successfully.'));
    }
}
