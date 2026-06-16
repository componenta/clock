<?php

declare(strict_types=1);

namespace Componenta\Clock;

use Psr\Clock\ClockInterface;

use function Componenta\Config\env;

class ConfigProvider extends \Componenta\Config\ConfigProvider
{
    protected function getFactories(): array
    {
        return [DateTimeFactory::class => static fn() => new DateTimeFactory(env('APP_TIMEZONE', 'UTC'))];
    }

    public function getAliases(): array
    {
        return [
            ClockInterface::class => DateTimeFactoryInterface::class,
            DateTimeFactoryInterface::class => DateTimeFactory::class,
        ];
    }
}
