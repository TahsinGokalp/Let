<?php

namespace Let\Tests;

class LoggingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        \Let\Facade::fake();

        $this->app['config']['logging.channels.let'] = ['driver' => 'let'];
        $this->app['config']['logging.default'] = 'let';
        $this->app['config']['let.environments'] = ['testing'];
    }

    /** @test */
    public function it_will_not_send_log_information_to_let()
    {
        $this->app['router']->get('/log-information-via-route/{type}', function (string $type) {
            \Illuminate\Support\Facades\Log::{$type}('log');
        });

        $this->get('/log-information-via-route/debug');
        $this->get('/log-information-via-route/info');
        $this->get('/log-information-via-route/notice');
        $this->get('/log-information-via-route/warning');
        $this->get('/log-information-via-route/error');
        $this->get('/log-information-via-route/critical');
        $this->get('/log-information-via-route/alert');
        $this->get('/log-information-via-route/emergency');

        \Let\Facade::assertRequestsSent(0);
    }

    /** @test */
    public function it_will_only_send_throwables_to_let()
    {
        $this->app['router']->get('/throwables-via-route', function () {
            throw new \Exception('exception-via-route');
        });

        $this->get('/throwables-via-route');

        \Let\Facade::assertRequestsSent(1);
    }
}
