<?php

return [
    'title' => 'Membership Types',
    'columns' => [
        'id' => 'ID',
        'name' => 'Name',
        'affiliates_required' => 'Required Affiliates',
        'cost' => 'Cost',
        'profit' => 'Profit',
        'created_at' => 'Created At',
        'actions' => 'Actions',
    ],
    'forms' => [
        'create_title' => 'Create Membership Type',
        'edit_title' => 'Edit Membership Type',
        'delete_title' => 'Delete Membership Type',
    ],
    'buttons' => [
        'create' => 'Create',
        'update' => 'Update',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'cancel' => 'Cancel',
        'cancel_edit' => 'Cancel edit',
        'back_to_memberships' => 'Back to Memberships',
    ],
    'messages' => [
        'empty' => 'No membership types available.',
        'created' => 'Membership type created successfully.',
        'updated' => 'Membership type updated successfully.',
        'deleted' => 'Membership type deleted successfully.',
        'delete_blocked' => 'Cannot delete this type because it has linked memberships.',
        'confirm_delete' => 'This action will delete the membership type. Continue?',
        'confirm_delete_modal' => 'This action will delete the selected membership type.',
    ],
    'types' => [
        'free' => 'Free',
        'customer' => 'Customer',
        'beginner' => 'Beginner',
        'constructor' => 'Constructor',
        'explorer' => 'Explorer',
        'professional' => 'Professional',
        'proffesional' => 'Professional',
        'elite' => 'Elite',
        'master' => 'Master',
        'legend' => 'Legend',
    ],
];
