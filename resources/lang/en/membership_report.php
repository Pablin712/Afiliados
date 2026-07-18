<?php

return [
    'title' => 'Membership Report',
    'subtitle' => 'Summary of users and memberships for the selected period.',
    'apply' => 'Apply range',
    'export_pdf' => 'Export PDF',
    'cards' => [
        'total_users' => 'Total users (to date)',
        'new_users' => 'New users in period',
        'paying_users' => 'Users with a paid membership',
        'free_users' => 'Users on the free plan',
        'upgrades' => 'New/upgraded plans in period',
        'non_renewed' => 'Did not renew in period',
    ],
    'sections' => [
        'type_breakdown' => 'Users by membership type',
        'status_breakdown' => 'Users by membership status',
        'upgrades' => 'New paid memberships in period',
        'non_renewed' => 'Users who did not renew in period',
    ],
    'columns' => [
        'type' => 'Type',
        'status' => 'Status',
        'total' => 'Total',
        'percent' => 'Percent',
        'user' => 'User',
        'email' => 'Email',
        'membership_type' => 'Membership type',
        'started_at' => 'Started',
        'previous_type' => 'Previous type',
        'downgraded_at' => 'Downgraded at',
    ],
    'statuses' => [
        'active' => 'Active',
        'free' => 'Free',
        'expired' => 'Expired',
        'pending_payment' => 'Pending payment',
    ],
    'messages' => [
        'empty' => 'No records for this period.',
        'report_generated_at' => 'Generated at',
        'non_renewed_hint' => 'Detected from the audit trail: users whose membership moved to free status during the period.',
        'permission_key' => 'report memberships',
    ],
];
