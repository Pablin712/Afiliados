<?php

return [
    'max_sponsor_levels' => (int) env('AFFILIATE_MAX_SPONSOR_LEVELS', 3),

    // Percentages are decimal fractions: 0.10 means 10%.
    'profit_distribution' => [
        1 => (float) env('AFFILIATE_LEVEL_1_PERCENT', 0.18),
        2 => (float) env('AFFILIATE_LEVEL_2_PERCENT', 0.05),
        3 => (float) env('AFFILIATE_LEVEL_3_PERCENT', 0.03),
    ],

    // Used by n8n or internal automations.
    'internal_api_token' => env('INTERNAL_API_TOKEN'),

    // n8n webhook that receives pending payments to trigger AI verification.
    'payment_verifier_webhook_url' => env('PAYMENT_VERIFIER_WEBHOOK_URL', 'https://autobot.aaronsoft.es/webhook/afiliados-payment-verifier'),
    'payment_verifier_webhook_token' => env('PAYMENT_VERIFIER_WEBHOOK_TOKEN'),
];
