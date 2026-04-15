<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * User-facing datetimes: values from MySQL are kept in UTC (session time_zone = +00:00).
 * Format in the configured display timezone (e.g. Asia/Dhaka).
 */
class DisplayTime
{
    public static function fromUtcStored(?string $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value, 'UTC')->timezone(config('app.display_timezone'));
    }
}
