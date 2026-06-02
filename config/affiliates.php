<?php

return [
    'max_sponsor_levels' => (int) env('AFFILIATE_MAX_SPONSOR_LEVELS', 7),

    // Network points granted per qualifying affiliate in the tree.
    'points_per_affiliate' => (int) env('AFFILIATE_POINTS_PER_AFFILIATE', 100),

    // Fixed commission amounts by network level.
    // Level 1 differentiates first activation (new) vs renewal/reactivation.
    'level_commissions' => [
        1 => [
            'new' => (float) env('AFFILIATE_LEVEL_1_NEW_AMOUNT', 22),
            'renewal' => (float) env('AFFILIATE_LEVEL_1_RENEWAL_AMOUNT', 18),
        ],
        2 => (float) env('AFFILIATE_LEVEL_2_AMOUNT', 12),
        3 => (float) env('AFFILIATE_LEVEL_3_AMOUNT', 8),
        4 => (float) env('AFFILIATE_LEVEL_4_AMOUNT', 6),
        5 => (float) env('AFFILIATE_LEVEL_5_AMOUNT', 3.5),
        6 => (float) env('AFFILIATE_LEVEL_6_AMOUNT', 2.5),
        7 => (float) env('AFFILIATE_LEVEL_7_AMOUNT', 1.2),
    ],

    // Monthly rank bonuses from explorer and above.
    // Promotion amounts are read from membership_types.profit.
    'rank_maintenance_bonuses' => [
        'explorer' => (float) env('AFFILIATE_EXPLORER_MAINTENANCE_BONUS', 20),
        'professional' => (float) env('AFFILIATE_PROFESSIONAL_MAINTENANCE_BONUS', 60),
        'elite' => (float) env('AFFILIATE_ELITE_MAINTENANCE_BONUS', 175),
        'master' => (float) env('AFFILIATE_MASTER_MAINTENANCE_BONUS', 400),
        'legend' => (float) env('AFFILIATE_LEGEND_MAINTENANCE_BONUS', 800),
    ],

    // Rank rules for user network progression (admin excluded from these rules).
    'rank_rules' => [
        'beginner' => [
            'direct_affiliates' => 1,
            'team_points' => 0,
            'direct_rank_min' => null,
            'direct_rank_count' => 0,
            'unlocks_level' => 1,
        ],
        'constructor' => [
            'direct_affiliates' => 3,
            'team_points' => 0,
            'direct_rank_min' => null,
            'direct_rank_count' => 0,
            'unlocks_level' => 2,
        ],
        'explorer' => [
            'direct_affiliates' => 5,
            'team_points' => 0,
            'direct_rank_min' => null,
            'direct_rank_count' => 0,
            'unlocks_level' => 3,
        ],
        'professional' => [
            'direct_affiliates' => 8,
            'team_points' => 1200,
            'direct_rank_min' => null,
            'direct_rank_count' => 0,
            'unlocks_level' => 4,
        ],
        'elite' => [
            'direct_affiliates' => 10,
            'team_points' => 2000,
            'direct_rank_min' => 2,
            'direct_rank_count' => 2,
            'unlocks_level' => 5,
        ],
        'master' => [
            'direct_affiliates' => 12,
            'team_points' => 4000,
            'direct_rank_min' => 4,
            'direct_rank_count' => 2,
            'unlocks_level' => 6,
        ],
        'legend' => [
            'direct_affiliates' => 15,
            'team_points' => 9000,
            'direct_rank_min' => 4,
            'direct_rank_count' => 3,
            'unlocks_level' => 7,
        ],
    ],

    // Used by n8n or internal automations.
    'internal_api_token' => env('INTERNAL_API_TOKEN'),

    // n8n webhook that receives pending payments to trigger AI verification.
    'payment_verifier_webhook_url' => env('PAYMENT_VERIFIER_WEBHOOK_URL', 'https://autobot.aaronsoft.es/webhook/afiliados-payment-verifier'),
    'payment_verifier_webhook_token' => env('PAYMENT_VERIFIER_WEBHOOK_TOKEN'),

    // Webhook used to send WhatsApp notification after successful registration.
    'registration_whatsapp_webhook_url' => env('REGISTRATION_WHATSAPP_WEBHOOK_URL'),
    'registration_whatsapp_webhook_token' => env('REGISTRATION_WHATSAPP_WEBHOOK_TOKEN'),
    'registration_whatsapp_default_country_code' => env('REGISTRATION_WHATSAPP_DEFAULT_COUNTRY_CODE', '593'),
];
