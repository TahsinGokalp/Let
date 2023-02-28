<?php

namespace Let\Tests\Fakes;

use LaraBug\Tests\TestCase;
use Let\Facade as LetFacade;

class LetFakeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        LetFacade::fake();

        $this->app['config']['logging.channels.let'] = ['driver' => 'let'];
        $this->app['config']['logging.default'] = 'let';
        $this->app['config']['let.environments'] = ['testing'];
    }

    /** @test */
    public function it_will_sent_exception_to_let_if_exception_is_thrown()
    {
        $this->app['router']->get('/exception', function () {
            throw new \Exception('Exception');
        });

        $this->get('/exception');

        LetFacade::assertSent(\Exception::class);

        LetFacade::assertSent(\Exception::class, function (\Throwable $throwable) {
            $this->assertSame('Exception', $throwable->getMessage());

            return true;
        });

        LetFacade::assertNotSent(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    }

    /** @test */
    public function it_will_sent_nothing_to_let_if_no_exceptions_thrown()
    {
        LetFacade::fake();

        $this->app['router']->get('/nothing', function () {
            //
        });

        $this->get('/nothing');

        LetFacade::assertNothingSent();
    }
}
