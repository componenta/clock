<?php

declare(strict_types=1);

namespace Componenta\Clock\Exception;

use InvalidArgumentException;
use Throwable;

/**
 * Exception thrown when an invalid timezone is provided.
 */
final class InvalidTimezoneException extends InvalidArgumentException
{
    public function __construct(
        public readonly string $timezone,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf("Invalid timezone '%s'", $timezone),
            0,
            $previous,
        );
    }
}