<?php

declare(strict_types=1);

namespace Componenta\Clock;

use Componenta\Config\ContainerValue;
use Psr\Clock\ClockInterface;

class ConfigProvider extends \Componenta\Config\ConfigProvider
{
    protected function getFactories(): array
    {
        return [
            DateTimeFactory::class => static fn(ContainerValue $container): DateTimeFactory =>
                new DateTimeFactory($container->config->environment->string('APP_TIMEZONE', 'UTC')),
        ];
    }

    public function getAliases(): array
    {
        return [
            ClockInterface::class => DateTimeFactoryInterface::class,
            DateTimeFactoryInterface::class => DateTimeFactory::class,
        ];
    }
}
