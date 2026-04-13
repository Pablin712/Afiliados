<?php

return [
    'title' => 'Memberships',
    'columns' => [
        'id' => 'ID',
        'user' => 'User',
        'membership_type' => 'Membership Type',
        'status' => 'Status',
        'started_at' => 'Started At',
        'expires_at' => 'Expires At',
        'created_at' => 'Created At',
        'actions' => 'Actions',
    ],
    'statuses' => [
        'active' => 'Active',
        'free' => 'Free',
        'expired' => 'Expired',
        'pending_payment' => 'Pending Payment',
    ],
    'buttons' => [
        'manage_membership_types' => 'Manage membership types',
        'apply_filters' => 'Apply filters',
        'clear_filters' => 'Clear',
        'save_membership' => 'Save changes',
    ],
    'filters' => [
        'all_statuses' => 'All statuses',
        'all_types' => 'All types',
    ],
    'messages' => [
        'description' => 'Membership list with user, type, status, and key lifecycle dates.',
        'empty' => 'No records available.',
        'report_generated_at' => 'Generated at',
        'report_permission' => 'You do not have the :permission permission required to export reports.',
        'permission_key' => 'report memberships',
        'updated' => 'Membership updated successfully.',
        'invalid_paid_status' => 'A paid membership cannot use free status. Please select a valid status.',
        'invalid_dates' => 'Expiration date cannot be earlier than start date.',
    ],
];
