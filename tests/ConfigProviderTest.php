<?php

declare(strict_types=1);

namespace Componenta\Clock\Tests;

use Componenta\Clock\DateTimeFactory;
use Componenta\Clock\DateTimeFactoryInterface;
use Componenta\Clock\ConfigProvider;
use Componenta\Config\ConfigKey;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

final class ConfigProviderTest extends TestCase
{
    public function testRegistersFactoryAndAliases(): void
    {
        $config = (new ConfigProvider())();
        $dependencies = $config[ConfigKey::DEPENDENCIES];

        self::assertArrayHasKey(DateTimeFactory::class, $dependencies[ConfigKey::FACTORIES]);
        self::assertSame(DateTimeFactoryInterface::class, $dependencies[ConfigKey::ALIASES][ClockInterface::class]);
        self::assertSame(DateTimeFactory::class, $dependencies[ConfigKey::ALIASES][DateTimeFactoryInterface::class]);
    }
}
