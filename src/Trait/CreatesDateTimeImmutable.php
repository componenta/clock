<?php

declare(strict_types=1);

namespace Componenta\Clock\Trait;

use Componenta\Clock\Exception\DateTimeParseException;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Throwable;

/**
 * Provides DateTimeImmutable creation methods.
 *
 * @property DateTimeZone $timezone
 */
trait CreatesDateTimeImmutable
{
    private(set) DateTimeZone $timezone;

    public function fromTimestamp(int $timestamp): DateTimeImmutable
    {
        return new DateTimeImmutable("@$timestamp")->setTimezone($this->timezone);
    }

    public function fromMicroTimestamp(float $timestamp): DateTimeImmutable
    {
        // Handle negative timestamps correctly
        // For -1.5: floor gives -2, then (-1.5 - -2) * 1_000_000 = 500_000
        $seconds = (int) floor($timestamp);
        $microseconds = (int) round(($timestamp - $seconds) * 1_000_000);

        // Normalize microseconds overflow (can happen due to rounding)
        if ($microseconds >= 1_000_000) {
            $seconds++;
            $microseconds -= 1_000_000;
        } elseif ($microseconds < 0) {
            $seconds--;
            $microseconds += 1_000_000;
        }

        /** @var DateTimeImmutable */
        $datetime = DateTimeImmutable::createFromFormat(
            'U u',
            sprintf('%d %06d', $seconds, $microseconds),
        );

        return $datetime->setTimezone($this->timezone);
    }

    public function fromFormat(string $format, string $datetime): DateTimeImmutable
    {
        if ($format === '') {
            throw new DateTimeParseException($datetime, $format, ['error' => 'Empty format string']);
        }

        $result = DateTimeImmutable::createFromFormat($format, $datetime, $this->timezone);

        if ($result === false) {
            throw DateTimeParseException::fromFormat($format, $datetime);
        }

        // Check for warnings (e.g., date overflow like Feb 30 -> Mar 2)
        $errors = DateTimeImmutable::getLastErrors();

        if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            throw DateTimeParseException::fromFormat($format, $datetime);
        }

        return $result;
    }

    public function parse(string $datetime): DateTimeImmutable
    {
        try {
            $result = new DateTimeImmutable($datetime, $this->timezone);
        } catch (Throwable $e) {
            throw DateTimeParseException::fromString($datetime, $e);
        }

        $errors = DateTimeImmutable::getLastErrors();

        if ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            throw DateTimeParseException::fromString($datetime);
        }

        return $result;
    }

    public function create(
        int $year,
        int $month,
        int $day,
        int $hour = 0,
        int $minute = 0,
        int $second = 0,
        int $microsecond = 0,
    ): DateTimeImmutable {
        $formatted = sprintf(
            '%04d-%02d-%02d %02d:%02d:%02d.%06d',
            $year,
            $month,
            $day,
            $hour,
            $minute,
            $second,
            $microsecond,
        );

        $result = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s.u',
            $formatted,
            $this->timezone,
        );

        if ($result === false) {
            throw DateTimeParseException::invalidComponents(
                $year,
                $month,
                $day,
                $hour,
                $minute,
                $second,
                $microsecond,
            );
        }

        return $result;
    }

    public function fromInterface(DateTimeInterface $datetime): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($datetime)
            ->setTimezone($this->timezone);
    }
}