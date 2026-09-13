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
 * A frozen datetime factory for testing purposes.
 *
 * Returns a fixed datetime from now() and all related methods,
 * allowing deterministic testing of time-dependent code.
 *
 * Behavior is intentionally identical to DateTimeFactory for all methods
 * except now(), today(), tomorrow(), yesterday(), and relative() which
 * use the frozen time as their reference point.
 */
final class FrozenClock implements DateTimeFactoryInterface
{
    use ResolvesTimezone, CreatesDateTimeImmutable;

    private(set) DateTimeImmutable $frozen;

    /**
     * @param DateTimeInterface|string|int $frozenAt Initial frozen time
     * @param DateTimeZone|string|null $timezone Timezone (null = system default)
     *
     * @throws InvalidTimezoneException When timezone string is invalid
     * @throws DateTimeParseException When frozenAt string is invalid
     */
    public function __construct(
        DateTimeInterface|string|int $frozenAt,
        DateTimeZone|string|null $timezone = null,
    ) {
        $this->timezone = self::resolveTimezone($timezone);
        $this->frozen = self::resolveDateTime($frozenAt, $this->timezone);
    }

    private static function resolveDateTime(
        DateTimeInterface|string|int $value,
        DateTimeZone $timezone,
    ): DateTimeImmutable {
        if ($value instanceof DateTimeImmutable) {
            return $value->setTimezone($timezone);
        }

        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value)->setTimezone($timezone);
        }

        if (is_int($value)) {
            return new DateTimeImmutable("@$value")->setTimezone($timezone);
        }

        // String - may throw on invalid input
        try {
            $result = new DateTimeImmutable($value, $timezone);
        } catch (Exception $e) {
            throw DateTimeParseException::fromString($value, $e);
        }

        // Check for warnings
        $errors = DateTimeImmutable::getLastErrors();

        if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            throw DateTimeParseException::fromString($value);
        }

        return $result->setTimezone($timezone);
    }

    /**
     * Creates a frozen clock at the current moment.
     */
    public static function atNow(DateTimeZone|string|null $timezone = null): self
    {
        return new self('now', $timezone);
    }

    /**
     * Creates a frozen clock at a specific date (midnight).
     */
    public static function atDate(
        int $year,
        int $month,
        int $day,
        DateTimeZone|string|null $timezone = null,
    ): self {
        return new self(
            sprintf('%04d-%02d-%02d 00:00:00', $year, $month, $day),
            $timezone,
        );
    }

    /**
     * Creates a frozen clock at a specific datetime.
     */
    public static function at(
        int $year,
        int $month,
        int $day,
        int $hour = 0,
        int $minute = 0,
        int $second = 0,
        DateTimeZone|string|null $timezone = null,
    ): self {
        return new self(
            sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second),
            $timezone,
        );
    }

    /**
     * Advances the frozen time by a modifier string.
     *
     * @throws DateTimeParseException When modifier is invalid
     */
    public function advance(string $modifier): void
    {
        try {
            $result = $this->frozen->modify($modifier);
        } catch (Exception) {
            throw DateTimeParseException::invalidModifier($modifier);
        }

        $this->frozen = $result;
    }

    /**
     * Sets a new frozen time.
     */
    public function freeze(DateTimeInterface|string|int $datetime): void
    {
        $this->frozen = self::resolveDateTime($datetime, $this->timezone);
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

        return new self($this->frozen, $resolved);
    }

    /**
     * Returns the frozen time (cloned per PSR-20 meta recommendation).
     */
    public function now(): DateTimeImmutable
    {
        return clone $this->frozen;
    }

    public function today(): DateTimeImmutable
    {
        return $this->frozen->setTime(0, 0, 0, 0);
    }

    public function tomorrow(): DateTimeImmutable
    {
        return $this->today()->modify('+1 day');
    }

    public function yesterday(): DateTimeImmutable
    {
        return $this->today()->modify('-1 day');
    }

    public function relative(string $modifier): DateTimeImmutable
    {
        try {
            $result = $this->frozen->modify($modifier);
        } catch (\Throwable) {
            throw DateTimeParseException::invalidModifier($modifier);
        }

        return $result;
    }
}