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

    public function regionPrices(): HasMany
    {
        return $this->hasMany(ProgramRegionPrice::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * A program either charges the same everywhere (its own costs) or defines
     * price rows for some regions, each listing the countries it applies to.
     */
    public function chargesByRegion(): bool
    {
        return $this->regionPrices()->exists();
    }

    /**
     * The region row that prices a buyer connecting from this country, or
     * null when no region lists it (or the country is unknown) and the
     * program's own costs apply. When two regions list the same country the
     * one with fewer countries wins, so an "Ecuador" row can carve a country
     * out of a "LATAM" one.
     */
    public function regionPriceForCountry(?string $country): ?ProgramRegionPrice
    {
        if ($country === null || $country === '') {
            return null;
        }

        return $this->regionPrices
            ->filter(fn (ProgramRegionPrice $row) => $row->covers($country))
            ->sortBy(fn (ProgramRegionPrice $row) => count($row->coveredCountries()))
            ->first();
    }

    /**
     * Amount for a bank transfer. The region row wins when there is one, so
     * programs without regions keep behaving exactly as before.
     */
    public function transferCostFor(?ProgramRegionPrice $region, bool $isRenewal): float
    {
        if ($region instanceof ProgramRegionPrice) {
            return $region->transferCost($isRenewal);
        }

        return (float) ($isRenewal ? $this->renewal_cost : $this->first_payment_cost);
    }

    /**
     * Amount for a card payment, or null when card payment is not offered for
     * this program (or for this region).
     */
    public function cardCostFor(?ProgramRegionPrice $region, bool $isRenewal): ?float
    {
        if ($region instanceof ProgramRegionPrice) {
            return $region->cardCost($isRenewal);
        }

        $value = $isRenewal ? $this->card_renewal_cost : $this->card_first_payment_cost;

        return $value === null ? null : (float) $value;
    }
}
