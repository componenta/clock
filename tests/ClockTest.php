<?php

declare(strict_types=1);

namespace Componenta\Clock\Tests;

use Componenta\Clock\Clock;
use PHPUnit\Framework\TestCase;

final class ClockTest extends TestCase
{
    public function testNowReturnsUtcDateTime(): void
    {
        $now = (new Clock())->now();

        self::assertSame('UTC', $now->getTimezone()->getName());
    }
}
