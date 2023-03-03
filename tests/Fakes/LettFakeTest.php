<?php

namespace Lett\Tests\Fakes;

use Lett\Facade as LettFacade;
use Lett\Tests\TestCase;
use RuntimeException;

class LettFakeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        LettFacade::fake();

        $this->app['config']['logging.channels.lett'] = ['driver' => 'lett'];
        $this->app['config']['logging.default'] = 'lett';
        $this->app['config']['lett.environments'] = ['testing'];
    }

    /** @test */
    public function it_will_sent_exception_to_let_if_exception_is_thrown()
    {
        $this->app['router']->get('/exception', function () {
            throw new RuntimeException('Exception');
        });

        $this->get('/exception');

        LettFacade::assertSent(\Exception::class);

        LettFacade::assertSent(\Exception::class, function (\Throwable $throwable) {
            $this->assertSame('Exception', $throwable->getMessage());

            return true;
        });

        LettFacade::assertNotSent(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    }

    /** @test */
    public function it_will_sent_nothing_to_let_if_no_exceptions_thrown()
    {
        LettFacade::fake();

        $this->app['router']->get('/nothing', function () {
            //
        });

        $this->get('/nothing');

        LettFacade::assertNothingSent();
    }
}
