<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDealRequest;
use App\Http\Requests\UpdateDealRequest;
use App\Http\Requests\UpdateDealStageRequest;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Lead;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Deal::class);

        $owners = User::query()->active()->orderBy('name')->get(['id', 'name']);
        $customers = Customer::query()->orderBy('name')->get(['id', 'name']);
        $stages = DealStage::query()->orderBy('position')->get();

        $deals = Deal::query()
            ->with(['customer', 'owner', 'stage', 'lead'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('stage_id'), fn ($query) => $query->where('stage_id', $request->integer('stage_id')))
            ->when($request->filled('owner_id'), fn ($query) => $query->where('owner_id', $request->integer('owner_id')))
            ->when($request->filled('customer_id'), fn ($query) => $query->where('customer_id', $request->integer('customer_id')))
            ->when(! $request->user()->isAdmin() && ! $request->user()->hasRole('manager'), fn ($query) => $query->where('owner_id', $request->user()->id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('crm.deals.index', compact('deals', 'owners', 'customers', 'stages'));
    }

    public function pipeline(Request $request)
    {
        abort_unless($request->user()->hasPermissionTo('deals.pipeline'), 403);

        $stages = DealStage::query()
            ->with(['deals' => function ($query) use ($request) {
                $query->with(['customer', 'owner', 'lead'])
                    ->when($request->filled('owner_id'), fn ($builder) => $builder->where('owner_id', $request->integer('owner_id')))
                    ->when($request->filled('customer_id'), fn ($builder) => $builder->where('customer_id', $request->integer('customer_id')))
                    ->orderByDesc('value');
            }])
            ->orderBy('position')
            ->get();

        $owners = User::query()->active()->orderBy('name')->get(['id', 'name']);
        $customers = Customer::query()->orderBy('name')->get(['id', 'name']);

        return view('crm.deals.pipeline', compact('stages', 'owners', 'customers'));
    }

    public function create()
    {
        $this->authorize('create', Deal::class);

        return view('crm.deals.create', [
            'leads' => Lead::query()->where('status', 'open')->orderBy('name')->get(['id', 'name']),
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'owners' => User::query()->active()->orderBy('name')->get(['id', 'name']),
            'stages' => DealStage::query()->orderBy('position')->get(),
        ]);
    }

    public function store(StoreDealRequest $request, AuditLogService $auditLogService)
    {
        $data = $request->validated();
        $data['owner_id'] ??= $request->user()->id;

        $stage = DealStage::query()->findOrFail($data['stage_id']);
        $data['status'] = $stage->is_won ? 'won' : ($stage->is_lost ? 'lost' : ($data['status'] ?? 'open'));

        $deal = Deal::query()->create($data);

        $auditLogService->record(
            module: 'crm',
            event: 'deal_created',
            auditable: $deal,
            newValues: $deal->toArray(),
            description: __('Deal :title created.', ['title' => $deal->title]),
        );

        return redirect()->route('deals.show', $deal)->with('success', __('Deal created successfully.'));
    }

    public function show(Deal $deal)
    {
        $this->authorize('view', $deal);

        $deal->load(['lead', 'customer', 'owner', 'stage']);

        return view('crm.deals.show', compact('deal'));
    }

    public function edit(Deal $deal)
    {
        $this->authorize('update', $deal);

        return view('crm.deals.edit', [
            'deal' => $deal,
            'leads' => Lead::query()->orderBy('name')->get(['id', 'name']),
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'owners' => User::query()->active()->orderBy('name')->get(['id', 'name']),
            'stages' => DealStage::query()->orderBy('position')->get(),
        ]);
    }

    public function update(UpdateDealRequest $request, Deal $deal, AuditLogService $auditLogService)
    {
        $this->authorize('update', $deal);

        $oldValues = $deal->toArray();
        $data = $request->validated();
        $stage = DealStage::query()->findOrFail($data['stage_id']);
        $data['status'] = $stage->is_won ? 'won' : ($stage->is_lost ? 'lost' : $data['status']);
        $data['closed_at'] = in_array($data['status'], ['won', 'lost'], true) ? now() : null;

        $deal->update($data);

        $auditLogService->record(
            module: 'crm',
            event: 'deal_updated',
            auditable: $deal,
            oldValues: $oldValues,
            newValues: $deal->fresh()->toArray(),
            description: __('Deal :title updated.', ['title' => $deal->title]),
        );

        return redirect()->route('deals.show', $deal)->with('success', __('Deal updated successfully.'));
    }

    public function updateStage(UpdateDealStageRequest $request, Deal $deal, AuditLogService $auditLogService)
    {
        $this->authorize('update', $deal);

        $stage = DealStage::query()->findOrFail($request->integer('stage_id'));
        $oldValues = $deal->only(['stage_id', 'status', 'closed_at']);

        $deal->update([
            'stage_id' => $stage->id,
            'status' => $stage->is_won ? 'won' : ($stage->is_lost ? 'lost' : 'open'),
            'closed_at' => ($stage->is_won || $stage->is_lost) ? now() : null,
        ]);

        $auditLogService->record(
            module: 'crm',
            event: 'deal_stage_updated',
            auditable: $deal,
            oldValues: $oldValues,
            newValues: $deal->fresh()->only(['stage_id', 'status', 'closed_at']),
            description: __('Deal :title moved to stage :stage.', ['title' => $deal->title, 'stage' => __($stage->name)]),
        );

        return back()->with('success', __('Deal stage updated successfully.'));
    }

    public function destroy(Deal $deal, AuditLogService $auditLogService)
    {
        $this->authorize('delete', $deal);

        $auditLogService->record(
            module: 'crm',
            event: 'deal_deleted',
            auditable: $deal,
            oldValues: $deal->toArray(),
            description: __('Deal :title deleted.', ['title' => $deal->title]),
        );

        $deal->delete();

        return redirect()->route('deals.index')->with('success', __('Deal deleted successfully.'));
    }
}
