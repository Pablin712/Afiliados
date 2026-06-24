<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'membership_type_id',
        'first_payment_cost',
        'card_first_payment_cost',
        'renewal_cost',
        'card_renewal_cost',
        'duration_months',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'first_payment_cost'      => 'decimal:2',
            'card_first_payment_cost' => 'decimal:2',
            'renewal_cost'            => 'decimal:2',
            'card_renewal_cost'       => 'decimal:2',
            'duration_months'         => 'integer',
            'is_active'               => 'boolean',
        ];
    }

    public function membershipType(): BelongsTo
    {
        return $this->belongsTo(MembershipType::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
