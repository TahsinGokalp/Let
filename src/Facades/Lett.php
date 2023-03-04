<?php

namespace TahsinGokalp\Lett\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \TahsinGokalp\Lett\Lett
 */
class Lett extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \TahsinGokalp\Lett\Lett::class;
    }
}
