<x-app-layout>
    <x-slot name="header">
        <x-page-header title="CRM Report" description="A compact view of customer and lead conversion health." />
    </x-slot>

    <div class="grid gap-4 md:grid-cols-5">
        <x-stat-card label="Customers" :value="$report['customers_count']" />
        <x-stat-card label="Potential" :value="$report['potential_customers']" accent="amber" />
        <x-stat-card label="Active" :value="$report['active_customers']" accent="emerald" />
        <x-stat-card label="Open Leads" :value="$report['lead_open_count']" accent="sky" />
        <x-stat-card label="Converted Leads" :value="$report['lead_converted_count']" accent="cyan" />
    </div>
</x-app-layout>
