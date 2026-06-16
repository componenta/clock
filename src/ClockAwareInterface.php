<?php

namespace Componenta\Clock;

use Psr\Clock\ClockInterface;

interface ClockAwareInterface
{
    public function setClock(ClockInterface $clock): void ;
}