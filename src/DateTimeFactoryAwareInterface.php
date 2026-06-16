<?php

namespace Componenta\Clock;

interface DateTimeFactoryAwareInterface
{
    public function setDateTimeFactory(DateTimeFactoryInterface $factory): void;
}