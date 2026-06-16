<?php

declare(strict_types=1);

namespace Componenta\Clock\Tests;

use Componenta\Clock\DateTimeFactory;
use Componenta\Clock\Exception\DateTimeParseException;
use Componenta\Clock\Exception\InvalidTimezoneException;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

final class DateTimeFactoryTest extends TestCase
{
    public function testUsesConfiguredTimezone(): void
    {
        $factory = new DateTimeFactory('Europe/Kaliningrad');

        self::assertSame('Europe/Kaliningrad', $factory->timezone->getName());
        self::assertSame('Europe/Kaliningrad', $factory->now()->getTimezone()->getName());
    }

    public function testWithTimezoneReturnsSameInstanceForSameTimezone(): void
    {
        $factory = new DateTimeFactory('UTC');

        self::assertSame($factory, $factory->withTimezone(new DateTimeZone('UTC')));
    }

    public function testWithTimezoneReturnsNewFactoryForDifferentTimezone(): void
    {
        $factory = new DateTimeFactory('UTC');
        $changed = $factory->withTimezone('Europe/Kaliningrad');

        self::assertNotSame($factory, $changed);
        self::assertSame('Europe/Kaliningrad', $changed->timezone->getName());
    }

    public function testCreatesFromTimestampInFactoryTimezone(): void
    {
        $factory = new DateTimeFactory('UTC');
        $datetime = $factory->fromTimestamp(0);

        self::assertSame('1970-01-01 00:00:00', $datetime->format('Y-m-d H:i:s'));
        self::assertSame('UTC', $datetime->getTimezone()->getName());
    }

    public function testCreatesFromMicroTimestamp(): void
    {
        $factory = new DateTimeFactory('UTC');
        $datetime = $factory->fromMicroTimestamp(1_700_000_000.123456);

        self::assertSame('2023-11-14 22:13:20.123456', $datetime->format('Y-m-d H:i:s.u'));
    }

    public function testParsesFromFormatStrictly(): void
    {
        $factory = new DateTimeFactory('UTC');
        $datetime = $factory->fromFormat('Y-m-d H:i:s', '2026-06-07 12:30:00');

        self::assertSame('2026-06-07 12:30:00', $datetime->format('Y-m-d H:i:s'));
    }

    public function testRejectsInvalidFormatInput(): void
    {
        $factory = new DateTimeFactory('UTC');

        $this->expectException(DateTimeParseException::class);

        $factory->fromFormat('Y-m-d', '2026-02-30');
    }

    public function testParsesStringAndConvertsInterfaceTimezone(): void
    {
        $factory = new DateTimeFactory('Europe/Kaliningrad');

        self::assertSame(
            '2026-06-07 10:00:00',
            $factory->parse('2026-06-07 10:00:00')->format('Y-m-d H:i:s'),
        );

        $datetime = new DateTimeImmutable('2026-06-07 08:00:00', new DateTimeZone('UTC'));

        self::assertSame(
            '2026-06-07 10:00:00',
            $factory->fromInterface($datetime)->format('Y-m-d H:i:s'),
        );
    }

    public function testRejectsInvalidTimezone(): void
    {
        $this->expectException(InvalidTimezoneException::class);

        new DateTimeFactory('Not/A_Timezone');
    }
}
