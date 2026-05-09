<?php

namespace App\Notifications;

use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeadConvertedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Lead $lead, private readonly Customer $customer)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'lead_converted',
            'title' => __('Lead converted'),
            'message' => __('Lead :lead has been converted to customer :customer.', ['lead' => $this->lead->name, 'customer' => $this->customer->name]),
            'url' => route('customers.show', $this->customer),
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
        ];
    }
}
