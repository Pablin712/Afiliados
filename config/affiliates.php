<?php

return [
    'max_sponsor_levels' => (int) env('AFFILIATE_MAX_SPONSOR_LEVELS', 3),

    // Percentages are decimal fractions: 0.10 means 10%.
    'profit_distribution' => [
        1 => (float) env('AFFILIATE_LEVEL_1_PERCENT', 0.10),
        2 => (float) env('AFFILIATE_LEVEL_2_PERCENT', 0.05),
        3 => (float) env('AFFILIATE_LEVEL_3_PERCENT', 0.03),
    ],

    // Used by n8n or internal automations.
    'internal_api_token' => env('INTERNAL_API_TOKEN'),
];
