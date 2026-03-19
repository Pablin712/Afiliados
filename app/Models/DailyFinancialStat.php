<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyFinancialStat extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'stat_date',
        'incomes_total',
        'expenses_total',
        'net_profit',
        'new_users_count',
        'new_customers_count',
        'approved_payments_count',
        'pending_profits_total',
        'profits_paid_total',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stat_date' => 'date',
            'incomes_total' => 'decimal:2',
            'expenses_total' => 'decimal:2',
            'net_profit' => 'decimal:2',
            'pending_profits_total' => 'decimal:2',
            'profits_paid_total' => 'decimal:2',
        ];
    }
}
