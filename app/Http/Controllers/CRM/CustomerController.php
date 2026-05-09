<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Customer::class);

        $users = User::query()->active()->orderBy('name')->get(['id', 'name']);

        $customers = Customer::query()
            ->with('owner')
            ->withCount(['deals', 'projects'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('owner_id'), fn ($query) => $query->where('owner_id', $request->integer('owner_id')))
            ->when(! $request->user()->isAdmin() && ! $request->user()->hasRole('manager'), fn ($query) => $query->where('owner_id', $request->user()->id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('crm.customers.index', compact('customers', 'users'));
    }

    public function create()
    {
        $this->authorize('create', Customer::class);

        $owners = User::query()->active()->orderBy('name')->get(['id', 'name']);

        return view('crm.customers.create', compact('owners'));
    }

    public function store(StoreCustomerRequest $request, AuditLogService $auditLogService)
    {
        $data = $request->validated();
        $data['owner_id'] ??= $request->user()->id;

        $customer = Customer::query()->create($data);

        $auditLogService->record(
            module: 'crm',
            event: 'customer_created',
            auditable: $customer,
            newValues: $customer->toArray(),
            description: __('Customer :name created.', ['name' => $customer->name]),
        );

        return redirect()->route('customers.show', $customer)->with('success', __('Customer created successfully.'));
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        $customer->load([
            'owner',
            'interactions.user',
            'followUps.assignee',
            'deals.stage',
            'deals.owner',
            'projects.manager',
            'projects.members',
        ]);

        return view('crm.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);

        $owners = User::query()->active()->orderBy('name')->get(['id', 'name']);

        return view('crm.customers.edit', compact('customer', 'owners'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer, AuditLogService $auditLogService)
    {
        $this->authorize('update', $customer);

        $oldValues = $customer->toArray();
        $customer->update($request->validated());

        $auditLogService->record(
            module: 'crm',
            event: 'customer_updated',
            auditable: $customer,
            oldValues: $oldValues,
            newValues: $customer->fresh()->toArray(),
            description: __('Customer :name updated.', ['name' => $customer->name]),
        );

        return redirect()->route('customers.show', $customer)->with('success', __('Customer updated successfully.'));
    }

    public function destroy(Customer $customer, AuditLogService $auditLogService)
    {
        $this->authorize('delete', $customer);

        $auditLogService->record(
            module: 'crm',
            event: 'customer_deleted',
            auditable: $customer,
            oldValues: $customer->toArray(),
            description: __('Customer :name deleted.', ['name' => $customer->name]),
        );

        $customer->delete();

        return redirect()->route('customers.index')->with('success', __('Customer deleted successfully.'));
    }
}
