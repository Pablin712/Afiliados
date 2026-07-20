<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipExpiryRun extends Model
{
    protected $fillable = [
        'run_at',
        'processed',
        'downgraded',
        'free_renewals',
        'downgraded_user_ids',
        'free_renewal_user_ids',
        'whatsapp_group_removed',
        'telegram_banned',
    ];

    protected function casts(): array
    {
        return [
            'run_at' => 'datetime',
            'downgraded_user_ids' => 'array',
            'free_renewal_user_ids' => 'array',
        ];
    }
}
