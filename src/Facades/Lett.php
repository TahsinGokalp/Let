<?php

namespace TahsinGokalp\Lett\Facades;

use Illuminate\Support\Facades\Facade;
use TahsinGokalp\Lett\Http\Client;
use TahsinGokalp\Lett\Tests\Fakes\LettFake;
use TahsinGokalp\Lett\Tests\Mocks\LettClient;

/**
 * @see \TahsinGokalp\Lett\Lett
 */
class Lett extends Facade
{
    public static function fake(): void
    {
        static::swap(new LettFake(new LettClient('login_key', 'project_key')));
    }

    protected static function getFacadeAccessor(): string
    {
        return 'lett';
    }
}
