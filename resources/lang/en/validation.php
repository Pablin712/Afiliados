<?php

return [
    'required' => 'The :attribute field is required.',
    'email' => 'The :attribute must be a valid email address.',
    'min' => 'The :attribute must be at least :min characters.',
    'max' => 'The :attribute must not exceed :max characters.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'unique' => 'The :attribute has already been taken.',
    'numeric' => 'The :attribute must be a number.',
    'password' => 'The password is incorrect.',

    'custom' => [
        'phone' => [
            'regex' => 'The phone number must be a valid international format (e.g. +12025550123).',
        ],
    ],

    'attributes' => [
        'name' => 'name',
        'email' => 'email',
        'password' => 'password',
        'phone' => 'phone',
        'identification' => 'identification',
        'sponsor_id' => 'sponsor',
        'commission_rate' => 'commission rate',
        'membership_type_id' => 'membership type',
    ],
];
