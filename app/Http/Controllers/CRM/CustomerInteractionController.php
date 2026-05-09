<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerInteractionRequest;
use App\Models\Customer;
use App\Services\AuditLogService;

class CustomerInteractionController extends Controller
{
    public function store(StoreCustomerInteractionRequest $request, AuditLogService $auditLogService)
    {
        $customer = Customer::query()->findOrFail($request->integer('customer_id'));
        $this->authorize('update', $customer);

        $interaction = $customer->interactions()->create([
            'user_id' => $request->user()->id,
            'type' => $request->string('type'),
            'subject' => $request->string('subject'),
            'details' => $request->string('details'),
            'interaction_at' => $request->date('interaction_at'),
        ]);

        $customer->update(['last_contacted_at' => now()]);

        $auditLogService->record(
            module: 'crm',
            event: 'interaction_created',
            auditable: $interaction,
            newValues: $interaction->toArray(),
            description: __('Interaction added to customer :name', ['name' => $customer->name]),
        );

        return back()->with('success', __('Interaction added successfully.'));
    }
}
