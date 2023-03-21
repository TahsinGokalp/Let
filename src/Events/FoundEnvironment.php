<?php

namespace TahsinGokalp\Lett\Events;

class FoundEnvironment
{
    public function __construct(public string $environment)
    {
    }
}
