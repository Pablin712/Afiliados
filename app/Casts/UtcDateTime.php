<?php

namespace App\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * The DB column stores datetimes as plain UTC strings (no offset). Laravel's
 * built-in 'datetime' cast parses raw values using the app timezone
 * (America/Guayaquil), silently misinterpreting them as local time instead of
 * UTC and shifting the represented instant by the timezone offset. This cast
 * parses/formats explicitly against UTC so the Carbon instance always
 * represents the correct real-world instant.
 */
class UtcDateTime implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d H:i:s', $value, 'UTC');
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return Carbon::parse($value)->utc()->format('Y-m-d H:i:s');
    }
}
