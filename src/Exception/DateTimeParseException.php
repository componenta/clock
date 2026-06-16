<?php

declare(strict_types=1);

namespace Componenta\Clock\Exception;

use DateTimeImmutable;
use InvalidArgumentException;
use Throwable;

/**
 * Exception thrown when datetime parsing or creation fails.
 */
final class DateTimeParseException extends InvalidArgumentException
{
    /**
     * @param string $input The input that failed to parse
     * @param string|null $format The format used (if any)
     * @param array<string, mixed> $context Additional context for debugging
     */
    public function __construct(
        public readonly string $input,
        public readonly ?string $format = null,
        public readonly array $context = [],
        ?string $message = null,
        ?Throwable $previous = null,
    ) {
        $message ??= $this->buildMessage();

        parent::__construct($message, 0, $previous);
    }

    private function buildMessage(): string
    {
        if ($this->format !== null) {
            return sprintf(
                "Failed to parse '%s' using format '%s'",
                $this->truncate($this->input),
                $this->format,
            );
        }

        return sprintf(
            "Failed to parse datetime string '%s'",
            $this->truncate($this->input),
        );
    }

    private function truncate(string $value, int $maxLength = 50): string
    {
        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return mb_substr($value, 0, $maxLength - 3) . '...';
    }

    public static function fromFormat(string $format, string $input): self
    {
        $errors = DateTimeImmutable::getLastErrors();

        return new self(
            input: $input,
            format: $format,
            context: $errors !== false ? $errors : [],
        );
    }

    public static function fromString(string $input, ?Throwable $previous = null): self
    {
        $errors = DateTimeImmutable::getLastErrors();

        return new self(
            input: $input,
            format: null,
            context: $errors !== false ? $errors : [],
            previous: $previous,
        );
    }

    public static function invalidModifier(string $modifier): self
    {
        return new self(
            input: $modifier,
            format: null,
            context: ['type' => 'relative_modifier'],
            message: sprintf("Invalid relative datetime modifier '%s'", $modifier),
        );
    }

    public static function invalidComponents(
        int $year,
        int $month,
        int $day,
        int $hour,
        int $minute,
        int $second,
        int $microsecond,
    ): self {
        $input = sprintf(
            '%04d-%02d-%02d %02d:%02d:%02d.%06d',
            $year,
            $month,
            $day,
            $hour,
            $minute,
            $second,
            $microsecond,
        );

        return new self(
            input: $input,
            format: 'Y-m-d H:i:s.u',
            context: [
                'type' => 'components',
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'hour' => $hour,
                'minute' => $minute,
                'second' => $second,
                'microsecond' => $microsecond,
            ],
        );
    }
}