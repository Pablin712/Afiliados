<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramRegionPrice extends Model
{
    protected $fillable = [
        'program_id',
        'region',
        'first_payment_cost',
        'card_first_payment_cost',
        'renewal_cost',
        'card_renewal_cost',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'first_payment_cost' => 'decimal:2',
            'card_first_payment_cost' => 'decimal:2',
            'renewal_cost' => 'decimal:2',
            'card_renewal_cost' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function transferCost(bool $isRenewal): float
    {
        return (float) ($isRenewal ? $this->renewal_cost : $this->first_payment_cost);
    }

    public function cardCost(bool $isRenewal): ?float
    {
        $value = $isRenewal ? $this->card_renewal_cost : $this->card_first_payment_cost;

        return $value === null ? null : (float) $value;
    }
}
