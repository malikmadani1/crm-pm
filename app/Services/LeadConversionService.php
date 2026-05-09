<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Lead;
use App\Notifications\LeadConvertedNotification;
use Illuminate\Support\Facades\DB;

class LeadConversionService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function convert(Lead $lead): Customer
    {
        return DB::transaction(function () use ($lead) {
            $customer = Customer::query()->create([
                'owner_id' => $lead->owner_id,
                'name' => $lead->name,
                'phone' => $lead->phone,
                'email' => $lead->email,
                'company_name' => $lead->company_name,
                'job_title' => $lead->job_title,
                'address' => $lead->address,
                'city' => $lead->city,
                'country' => $lead->country,
                'status' => 'active',
                'notes' => $lead->notes,
                'last_contacted_at' => now(),
            ]);

            $lead->update([
                'converted_customer_id' => $customer->id,
                'status' => 'converted',
                'stage' => 'won',
                'converted_at' => now(),
            ]);

            if ($lead->owner) {
                $lead->owner->notify(new LeadConvertedNotification($lead, $customer));
            }

            $this->auditLogService->record(
                module: 'crm',
                event: 'lead_converted',
                auditable: $lead,
                oldValues: null,
                newValues: $lead->fresh()->toArray(),
                description: "Lead #{$lead->id} converted to customer #{$customer->id}",
            );

            return $customer;
        });
    }
}
