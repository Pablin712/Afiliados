<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'affiliates_required',
        'cost',
        'profit',
    ];

    protected function casts(): array
    {
        return [
            'affiliates_required' => 'integer',
            'cost' => 'decimal:2',
            'profit' => 'decimal:2',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }
}
