<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConvertLeadRequest;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\LeadConversionService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Lead::class);

        $owners = User::query()->active()->orderBy('name')->get(['id', 'name']);

        $leads = Lead::query()
            ->with(['owner', 'customer'])
            ->withCount(['followUps', 'deals'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('stage'), fn ($query) => $query->where('stage', $request->string('stage')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('owner_id'), fn ($query) => $query->where('owner_id', $request->integer('owner_id')))
            ->when(! $request->user()->isAdmin() && ! $request->user()->hasRole('manager'), fn ($query) => $query->where('owner_id', $request->user()->id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('crm.leads.index', compact('leads', 'owners'));
    }

    public function create()
    {
        $this->authorize('create', Lead::class);

        $owners = User::query()->active()->orderBy('name')->get(['id', 'name']);

        return view('crm.leads.create', compact('owners'));
    }

    public function store(StoreLeadRequest $request, AuditLogService $auditLogService)
    {
        $data = $request->validated();
        $data['owner_id'] ??= $request->user()->id;
        $lead = Lead::query()->create($data);

        $auditLogService->record(
            module: 'crm',
            event: 'lead_created',
            auditable: $lead,
            newValues: $lead->toArray(),
            description: __('Lead :name created.', ['name' => $lead->name]),
        );

        return redirect()->route('leads.show', $lead)->with('success', __('Lead created successfully.'));
    }

    public function show(Lead $lead)
    {
        $this->authorize('view', $lead);

        $lead->load(['owner', 'customer', 'followUps.assignee', 'deals.stage', 'deals.owner']);

        return view('crm.leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $this->authorize('update', $lead);

        $owners = User::query()->active()->orderBy('name')->get(['id', 'name']);

        return view('crm.leads.edit', compact('lead', 'owners'));
    }

    public function update(UpdateLeadRequest $request, Lead $lead, AuditLogService $auditLogService)
    {
        $this->authorize('update', $lead);

        $oldValues = $lead->toArray();
        $lead->update($request->validated());

        $auditLogService->record(
            module: 'crm',
            event: 'lead_updated',
            auditable: $lead,
            oldValues: $oldValues,
            newValues: $lead->fresh()->toArray(),
            description: __('Lead :name updated.', ['name' => $lead->name]),
        );

        return redirect()->route('leads.show', $lead)->with('success', __('Lead updated successfully.'));
    }

    public function convert(ConvertLeadRequest $request, Lead $lead, LeadConversionService $leadConversionService)
    {
        $this->authorize('update', $lead);

        $customer = $leadConversionService->convert($lead);

        return redirect()->route('customers.show', $customer)->with('success', __('Lead converted successfully.'));
    }

    public function destroy(Lead $lead, AuditLogService $auditLogService)
    {
        $this->authorize('delete', $lead);

        $auditLogService->record(
            module: 'crm',
            event: 'lead_deleted',
            auditable: $lead,
            oldValues: $lead->toArray(),
            description: __('Lead :name deleted.', ['name' => $lead->name]),
        );

        $lead->delete();

        return redirect()->route('leads.index')->with('success', __('Lead deleted successfully.'));
    }
}
