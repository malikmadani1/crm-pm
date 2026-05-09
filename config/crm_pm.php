<?php

return [
    'permissions' => [
        'dashboard' => ['view'],
        'users' => ['view', 'create', 'update', 'delete'],
        'roles' => ['view', 'create', 'update', 'delete'],
        'customers' => ['view', 'create', 'update', 'delete'],
        'leads' => ['view', 'create', 'update', 'delete', 'convert'],
        'deals' => ['view', 'create', 'update', 'delete', 'pipeline'],
        'projects' => ['view', 'create', 'update', 'delete'],
        'tasks' => ['view', 'create', 'update', 'delete', 'assign', 'comment', 'move'],
        'time_entries' => ['view', 'create', 'update', 'delete'],
        'reports' => ['view', 'export'],
        'notifications' => ['view'],
        'audit_logs' => ['view'],
    ],
    'deal_stages' => [
        ['name' => 'New Lead', 'slug' => 'new_lead', 'color' => 'slate', 'position' => 1, 'is_won' => false, 'is_lost' => false],
        ['name' => 'Contacted', 'slug' => 'contacted', 'color' => 'sky', 'position' => 2, 'is_won' => false, 'is_lost' => false],
        ['name' => 'Qualified', 'slug' => 'qualified', 'color' => 'indigo', 'position' => 3, 'is_won' => false, 'is_lost' => false],
        ['name' => 'Proposal Sent', 'slug' => 'proposal_sent', 'color' => 'amber', 'position' => 4, 'is_won' => false, 'is_lost' => false],
        ['name' => 'Negotiation', 'slug' => 'negotiation', 'color' => 'orange', 'position' => 5, 'is_won' => false, 'is_lost' => false],
        ['name' => 'Won', 'slug' => 'won', 'color' => 'emerald', 'position' => 6, 'is_won' => true, 'is_lost' => false],
        ['name' => 'Lost', 'slug' => 'lost', 'color' => 'rose', 'position' => 7, 'is_won' => false, 'is_lost' => true],
    ],
    'labels' => [
        'customer_statuses' => [
            'potential' => ['label' => 'Potential', 'color' => 'amber'],
            'active' => ['label' => 'Active', 'color' => 'emerald'],
            'not_interested' => ['label' => 'Not Interested', 'color' => 'rose'],
        ],
        'lead_stages' => [
            'new_lead' => ['label' => 'New Lead', 'color' => 'slate'],
            'contacted' => ['label' => 'Contacted', 'color' => 'sky'],
            'qualified' => ['label' => 'Qualified', 'color' => 'indigo'],
            'proposal_sent' => ['label' => 'Proposal Sent', 'color' => 'amber'],
            'negotiation' => ['label' => 'Negotiation', 'color' => 'orange'],
            'won' => ['label' => 'Won', 'color' => 'emerald'],
            'lost' => ['label' => 'Lost', 'color' => 'rose'],
        ],
        'project_statuses' => [
            'in_progress' => ['label' => 'In Progress', 'color' => 'sky'],
            'completed' => ['label' => 'Completed', 'color' => 'emerald'],
            'paused' => ['label' => 'Paused', 'color' => 'amber'],
            'on_hold' => ['label' => 'On Hold', 'color' => 'rose'],
        ],
        'task_statuses' => [
            'todo' => ['label' => 'To Do', 'color' => 'slate'],
            'in_progress' => ['label' => 'In Progress', 'color' => 'sky'],
            'review' => ['label' => 'Review', 'color' => 'amber'],
            'done' => ['label' => 'Done', 'color' => 'emerald'],
        ],
        'priorities' => [
            'low' => ['label' => 'Low', 'color' => 'slate'],
            'medium' => ['label' => 'Medium', 'color' => 'amber'],
            'high' => ['label' => 'High', 'color' => 'rose'],
        ],
        'follow_up_statuses' => [
            'pending' => ['label' => 'Pending', 'color' => 'amber'],
            'completed' => ['label' => 'Completed', 'color' => 'emerald'],
            'cancelled' => ['label' => 'Cancelled', 'color' => 'rose'],
        ],
    ],
];
