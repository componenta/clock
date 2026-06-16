<?php

declare(strict_types=1);

namespace Componenta\Clock\Tests;

use Componenta\Clock\FrozenClock;
use PHPUnit\Framework\TestCase;

final class FrozenClockTest extends TestCase
{
    public function testNowReturnsFrozenClone(): void
    {
        $clock = new FrozenClock('2026-06-07 12:00:00', 'UTC');

        $first = $clock->now();
        $second = $clock->now();

        self::assertEquals($first, $second);
        self::assertNotSame($first, $second);
        self::assertSame('2026-06-07 12:00:00', $first->format('Y-m-d H:i:s'));
    }

    public function testAdvanceAndFreezeMutateFrozenTimeExplicitly(): void
    {
        $clock = new FrozenClock('2026-06-07 12:00:00', 'UTC');

        $clock->advance('+2 hours');
        self::assertSame('2026-06-07 14:00:00', $clock->now()->format('Y-m-d H:i:s'));

        $clock->freeze('2026-06-08 09:15:00');
        self::assertSame('2026-06-08 09:15:00', $clock->now()->format('Y-m-d H:i:s'));
    }

    public function testRelativeHelpersUseFrozenReference(): void
    {
        $clock = new FrozenClock('2026-06-07 12:00:00', 'UTC');

        self::assertSame('2026-06-07 00:00:00', $clock->today()->format('Y-m-d H:i:s'));
        self::assertSame('2026-06-08 00:00:00', $clock->tomorrow()->format('Y-m-d H:i:s'));
        self::assertSame('2026-06-06 00:00:00', $clock->yesterday()->format('Y-m-d H:i:s'));
        self::assertSame('2026-06-10 12:00:00', $clock->relative('+3 days')->format('Y-m-d H:i:s'));
    }
}
