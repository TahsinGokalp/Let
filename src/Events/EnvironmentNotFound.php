<?php

namespace TahsinGokalp\Lett\Events;

class EnvironmentNotFound
{
    public function __construct(public string $environment)
    {
    }
}
