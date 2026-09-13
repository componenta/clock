<?php

declare(strict_types=1);

namespace Componenta\Clock\Tests;

use Componenta\Clock\DateTimeFactory;
use Componenta\Clock\FrozenClock;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FactoryTimezoneTest extends TestCase
{
    /** @return iterable<string, array{bool, string}> */
    public static function parsers(): iterable
    {
        foreach ([false, true] as $frozen) {
            foreach (['parse', 'fromFormat'] as $method) {
                yield ($frozen ? 'frozen ' : 'live ') . $method => [$frozen, $method];
            }
        }
    }

    #[DataProvider('parsers')]
    public function testExplicitInputOffsetIsConvertedWithoutChangingTheInstant(bool $frozen, string $method): void
    {
        $factory = $frozen ? new FrozenClock('2026-01-01', 'UTC') : new DateTimeFactory('UTC');
        $input = '2026-06-07T01:30:00.123456+03:00';

        $result = $method === 'parse'
            ? $factory->parse($input)
            : $factory->fromFormat('Y-m-d\\TH:i:s.uP', $input);

        self::assertSame('2026-06-06 22:30:00.123456+00:00', $result->format('Y-m-d H:i:s.uP'));
        self::assertSame('UTC', $result->getTimezone()->getName());
    }

    /** @return iterable<string, array{bool}> */
    public static function frozenAssignments(): iterable
    {
        yield 'constructor' => [false];
        yield 'freeze' => [true];
    }

    #[DataProvider('frozenAssignments')]
    public function testFrozenStringAndObjectUseTheSameConfiguredTimezone(bool $freeze): void
    {
        $input = '2026-06-07T01:30:00.123456+03:00';
        $fromObject = new FrozenClock(new DateTimeImmutable($input), 'UTC');
        $fromString = new FrozenClock($freeze ? '2026-01-01' : $input, 'UTC');

        if ($freeze) {
            $fromString->freeze($input);
        }

        self::assertSame('2026-06-06 22:30:00.123456+00:00', $fromObject->now()->format('Y-m-d H:i:s.uP'));
        self::assertSame('2026-06-06 22:30:00.123456+00:00', $fromString->now()->format('Y-m-d H:i:s.uP'));
        self::assertSame('UTC', $fromString->now()->getTimezone()->getName());
        self::assertSame('2026-06-06 00:00:00+00:00', $fromString->today()->format('Y-m-d H:i:sP'));
    }
}
