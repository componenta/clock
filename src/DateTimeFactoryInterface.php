<?php

declare(strict_types=1);

namespace Componenta\Clock;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Psr\Clock\ClockInterface;

/**
 * Factory interface for creating DateTimeImmutable instances.
 *
 * Extends PSR-20 ClockInterface to provide comprehensive datetime creation
 * capabilities while maintaining testability through clock abstraction.
 *
 * All implementations MUST behave consistently:
 * - Invalid input MUST throw DateTimeParseException
 * - Timezone MUST be applied to all created instances
 * - Methods MUST NOT have side effects beyond object creation
 */
interface DateTimeFactoryInterface extends ClockInterface
{
    /**
     * Returns the current timezone used by the factory.
     */
    public DateTimeZone $timezone { get; }

    /**
     * Creates a new factory instance with the specified timezone.
     *
     * MUST return the same instance if timezone matches current.
     *
     * @throws Exception\InvalidTimezoneException When timezone string is invalid
     */
    public function withTimezone(DateTimeZone|string $timezone): self;

    /**
     * Creates a datetime from a Unix timestamp.
     *
     * @param int $timestamp Unix timestamp (seconds since epoch), may be negative
     */
    public function fromTimestamp(int $timestamp): DateTimeImmutable;

    /**
     * Creates a datetime from a Unix timestamp with microsecond precision.
     *
     * Accepts a float where the integer part is seconds and the fractional
     * part represents microseconds (e.g., 1234567890.123456).
     *
     * @param float $timestamp Unix timestamp with microsecond precision, may be negative
     */
    public function fromMicroTimestamp(float $timestamp): DateTimeImmutable;

    /**
     * Creates a datetime from a formatted string.
     *
     * @param string $format DateTime format string (must not be empty)
     * @param string $datetime The datetime string to parse
     *
     * @throws Exception\DateTimeParseException When parsing fails
     */
    public function fromFormat(string $format, string $datetime): DateTimeImmutable;

    /**
     * Parses a datetime string using automatic format detection.
     *
     * Supports ISO 8601, RFC 2822, RFC 3339, and common datetime formats.
     *
     * @param string $datetime The datetime string to parse
     *
     * @throws Exception\DateTimeParseException When parsing fails
     */
    public function parse(string $datetime): DateTimeImmutable;

    /**
     * Creates a datetime from individual components.
     *
     * PHP's datetime handling normalizes overflow values (e.g., month 13
     * becomes January of next year, day 32 becomes next month).
     * The PHPDoc ranges are recommendations for valid dates, not enforced constraints.
     *
     * Note: Due to PHP's normalization behavior, this method rarely throws.
     * Use validation before calling if strict date checking is required.
     *
     * @param int<1, 9999> $year
     * @param int<1, 12> $month
     * @param int<1, 31> $day
     * @param int<0, 23> $hour
     * @param int<0, 59> $minute
     * @param int<0, 59> $second
     * @param int<0, 999999> $microsecond
     *
     * @throws Exception\DateTimeParseException On catastrophic failure (extremely rare)
     */
    public function create(
        int $year,
        int $month,
        int $day,
        int $hour = 0,
        int $minute = 0,
        int $second = 0,
        int $microsecond = 0,
    ): DateTimeImmutable;

    /**
     * Converts any DateTimeInterface to DateTimeImmutable in factory's timezone.
     */
    public function fromInterface(DateTimeInterface $datetime): DateTimeImmutable;

    /**
     * Creates a datetime relative to now using a modifier string.
     *
     * @param string $modifier A relative datetime modifier (e.g., '+1 day', 'next monday')
     *
     * @throws Exception\DateTimeParseException When modifier is invalid
     *
     * @see https://www.php.net/manual/en/datetime.formats.relative.php
     */
    public function relative(string $modifier): DateTimeImmutable;
}