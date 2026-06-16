<?php

declare(strict_types=1);

namespace Componenta\Clock;

use Componenta\Clock\Exception\DateTimeParseException;
use Componenta\Clock\Exception\InvalidTimezoneException;
use Componenta\Clock\Trait\CreatesDateTimeImmutable;
use Componenta\Clock\Trait\ResolvesTimezone;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;

/**
 * Default implementation of DateTimeFactoryInterface.
 *
 * Provides comprehensive datetime creation with timezone management
 * and full PSR-20 ClockInterface compatibility.
 */
final class DateTimeFactory implements DateTimeFactoryInterface
{
    use ResolvesTimezone, CreatesDateTimeImmutable;

    /**
     * @param DateTimeZone|string|null $timezone Timezone for created datetimes (null = system default)
     *
     * @throws InvalidTimezoneException When timezone string is invalid
     */
    public function __construct(DateTimeZone|string|null $timezone = null)
    {
        $this->timezone = self::resolveTimezone($timezone);
    }

    public function getTimezone(): DateTimeZone
    {
        return $this->timezone;
    }

    public function withTimezone(DateTimeZone|string $timezone): self
    {
        $resolved = self::resolveTimezone($timezone);

        if ($resolved->getName() === $this->timezone->getName()) {
            return $this;
        }

        return new self($resolved);
    }

    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->timezone);
    }

    public function today(): DateTimeImmutable
    {
        return $this->now()->setTime(0, 0, 0, 0);
    }

    public function tomorrow(): DateTimeImmutable
    {
        return $this->today()->modify('+1 day');
    }

    public function yesterday(): DateTimeImmutable
    {
        return $this->today()->modify('-1 day');
    }

    public function fromInterface(DateTimeInterface $datetime): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($datetime)
            ->setTimezone($this->timezone);
    }

    public function relative(string $modifier): DateTimeImmutable
    {
        try {
            return $this->now()->modify($modifier);
        } catch (Exception) {
            throw DateTimeParseException::invalidModifier($modifier);
        }
    }
}