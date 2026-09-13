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

    #[\PHPUnit\Framework\Attributes\DataProvider('runtimeEnvironments')]
    public function testFactoryUsesTheRuntimeEnvironment(array $values, string $timezone): void
    {
        $composition = (new \Componenta\Config\ConfigFactory())->create(
            new \Componenta\Config\Environment($values),
            new ConfigProvider(),
        );
        $container = new \Componenta\Config\ContainerValue(
            $this->createStub(\Psr\Container\ContainerInterface::class),
            $composition->config,
        );
        $factory = $composition->dependencies->sections[ConfigKey::FACTORIES][DateTimeFactory::class];

        self::assertSame($timezone, $factory($container)->now()->getTimezone()->getName());
    }

    public static function runtimeEnvironments(): iterable
    {
        yield 'UTC default' => [[], 'UTC'];
        yield 'explicit timezone' => [['APP_TIMEZONE' => 'Europe/Kaliningrad'], 'Europe/Kaliningrad'];
    }
}
