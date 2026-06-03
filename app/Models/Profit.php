<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profit extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'user_bank_id',
        'transaction_id',
        'source_payment_id',
        'source_user_id',
        'source_level',
        'period_month',
        'amount',
        'state',
        'detail',
        'paid_by',
        'paid_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_month' => 'date',
            'amount' => 'decimal:2',
            'source_level' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userBank(): BelongsTo
    {
        return $this->belongsTo(UserBank::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function sourcePayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'source_payment_id');
    }

    public function sourceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'source_user_id');
    }

    /**
     * @return array{type: string, level?: int, payment_type?: string, source_user_name?: string|null, source_payment_id?: int|null, kind?: string, rank_name?: string}
     */
    public function parsedReason(): array
    {
        $detail = (string) ($this->detail ?? '');

        if (str_starts_with($detail, 'rank_bonus|')) {
            $parts = explode('|', $detail, 4);
            return [
                'type'      => 'rank_bonus',
                'kind'      => $parts[1] ?? '',
                'rank_name' => $parts[2] ?? '',
            ];
        }

        return [
            'type'              => 'level_commission',
            'level'             => (int) ($this->source_level ?? 0),
            'payment_type'      => str_contains($detail, 'new activation') ? 'new' : 'renewal',
            'source_user_name'  => $this->sourceUser?->name,
            'source_payment_id' => $this->source_payment_id,
        ];
    }
}
