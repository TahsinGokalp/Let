<?php

namespace Lett\Tests;

class LoggingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        \Lett\Facade::fake();

        $this->app['config']['logging.channels.lett'] = ['driver' => 'lett'];
        $this->app['config']['logging.default'] = 'lett';
        $this->app['config']['lett.environments'] = ['testing'];
    }

    /** @test */
    public function it_will_not_send_log_information_to_lett()
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

        \Lett\Facade::assertRequestsSent(0);
    }

    /** @test */
    public function it_will_only_send_throwables_to_lett()
    {
        $this->app['router']->get('/throwables-via-route', function () {
            throw new \Exception('exception-via-route');
        });

        $this->get('/throwables-via-route');

        \Lett\Facade::assertRequestsSent(1);
    }
}
