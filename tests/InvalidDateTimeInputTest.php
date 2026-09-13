<?php
declare(strict_types=1);
namespace Componenta\Clock\Tests;

use Componenta\Clock\DateTimeFactory;
use Componenta\Clock\DateTimeFactoryInterface;
use Componenta\Clock\Exception\DateTimeParseException;
use Componenta\Clock\FrozenClock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ValueError;

final class InvalidDateTimeInputTest extends TestCase
{
    public static function factories(): array
    {
        return ['live' => [false], 'frozen' => [true]];
    }

    private function factory(bool $frozen): DateTimeFactoryInterface
    {
        return $frozen ? new FrozenClock('2026-01-01', 'UTC') : new DateTimeFactory('UTC');
    }

    #[DataProvider('factories')]
    public function testFormattedInputErrorsUseTheFactoryException(bool $frozen): void
    {
        $input = "2026-01-01\0";
        try {
            $this->factory($frozen)->fromFormat('Y-m-d', $input);
            self::fail('Invalid input must throw DateTimeParseException.');
        } catch (DateTimeParseException $error) {
            self::assertSame($input, $error->input);
            self::assertSame('Y-m-d', $error->format);
            self::assertInstanceOf(ValueError::class, $error->getPrevious());
        }
    }

    public static function invalidTimestamps(): iterable
    {
        foreach (['not a number' => NAN, 'positive infinity' => INF, 'negative infinity' => -INF, 'positive overflow' => 1e30, 'negative overflow' => -1e30] as $name => $timestamp) {
            yield 'live ' . $name => [false, $timestamp];
            yield 'frozen ' . $name => [true, $timestamp];
        }
    }

    #[DataProvider('invalidTimestamps')]
    public function testInvalidTimestampsAreNotConvertedToAnUnrelatedDate(bool $frozen, float $timestamp): void
    {
        $this->expectException(DateTimeParseException::class);
        $this->factory($frozen)->fromMicroTimestamp($timestamp);
    }
}
