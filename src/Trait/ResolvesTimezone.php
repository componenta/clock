<?php

declare(strict_types=1);

namespace Componenta\Clock\Trait;

use Componenta\Clock\Exception\InvalidTimezoneException;
use DateTimeZone;
use Throwable;

/**
 * Provides timezone resolution from various input types.
 */
trait ResolvesTimezone
{
    private static function resolveTimezone(DateTimeZone|string|null $timezone): DateTimeZone
    {
        if ($timezone instanceof DateTimeZone) {
            return $timezone;
        }

        if ($timezone === null) {
            $timezone = date_default_timezone_get();
        }

        try {
            return new DateTimeZone($timezone);
        } catch (Throwable $e) {
            throw new InvalidTimezoneException($timezone, $e);
        }
    }
}