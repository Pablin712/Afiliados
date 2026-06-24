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

    // Telegram group management via Bot API.
    // Group chat IDs are negative integers for supergroups.
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
        'groups' => [
            'aet_premium'       => env('TELEGRAM_GROUP_AET_PREMIUM', '-5279685071'),
            'aet_vip_deriv'     => env('TELEGRAM_GROUP_AET_VIP_DERIV', '-1003633952853'),
            'aet_vip_weltrade'  => env('TELEGRAM_GROUP_AET_VIP_WELTRADE', '-1003742317642'),
        ],
    ],

    // WhatsApp group management via Evolution API.
    // The URL and group_jid are fixed for AET-SAS; only the apikey is a secret.
    'whatsapp_group' => [
        'url'       => env('WHATSAPP_GROUP_URL', 'https://evoapi.abigailsoft.com/group/updateParticipant/AET-SAS'),
        'group_jid' => env('WHATSAPP_GROUP_JID', '120363425909738995@g.us'),
        'apikey'    => env('WHATSAPP_GROUP_APIKEY', ''),
    ],

    // Datafast / Dataweb payment gateway (Botón de Pagos).
    // Fase 1 test: base_url = https://eu-test.oppwa.com (no testMode needed)
    // Fase 2 test: base_url = https://eu-test.oppwa.com + test_mode = EXTERNAL
    // Production:  base_url = provided by Datafast, test_mode = null
    'datafast' => [
        'enabled'          => (bool) env('DATAFAST_ENABLED', false),
        'base_url'         => env('DATAFAST_BASE_URL', 'https://eu-test.oppwa.com'),
        'entity_id'        => env('DATAFAST_ENTITY_ID', ''),
        'bearer_token'     => env('DATAFAST_BEARER_TOKEN', ''),
        'shopper_mid'      => env('DATAFAST_SHOPPER_MID', ''),
        'shopper_tid'      => env('DATAFAST_SHOPPER_TID', ''),
        'shopper_eci'      => env('DATAFAST_SHOPPER_ECI', '0'),
        'shopper_pserv'    => env('DATAFAST_SHOPPER_PSERV', '9999'),
        'shopper_version'  => env('DATAFAST_SHOPPER_VERSIONDF', '2'),
        'currency'         => env('DATAFAST_CURRENCY', 'USD'),
        'payment_type'     => env('DATAFAST_PAYMENT_TYPE', 'DB'),
        'brands'           => env('DATAFAST_BRANDS', 'VISA MASTER DINERS AMEX'),
        // IVA rate used to split the amount for SRI declarations (e.g. 0.15 for 15%).
        // Set 0 to send the full amount as BASE0 (zero-rated).
        'iva_rate'         => (float) env('DATAFAST_IVA_RATE', 0.15),
        // Fase 2 only: 'EXTERNAL'. Leave null in production.
        'test_mode'        => env('DATAFAST_TEST_MODE'),
        // Commerce name shown in risk parameters and widget.
        'commerce_name'    => env('DATAFAST_COMMERCE_NAME', ''),
        // Dev only: skip Datafast verification and simulate approval. NEVER true in production.
        'dev_bypass'       => (bool) env('DATAFAST_DEV_BYPASS', false),
    ],
];
