<?php

namespace TahsinGokalp\Lett;

use TahsinGokalp\Lett\Fakes\LettFake;
use TahsinGokalp\Lett\Http\Client;

/**
 * @method static void  assertSent($throwable, $callback = null)
 * @method static array requestsSent()
 * @method static void  assertNotSent($throwable, $callback = null)
 * @method static void  assertNothingSent()
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return void
     */
    public static function fake(): void
    {
        static::swap(new LettFake(new Client('login_key', 'project_key')));
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'lett';
    }
}
