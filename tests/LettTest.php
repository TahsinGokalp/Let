<?php

namespace Lett\Tests;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Lett\Lett;
use Lett\Tests\Mocks\LettClient;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LettTest extends TestCase
{
    /** @var Lett */
    protected Lett $lett;

    /** @var Mocks\LettClient */
    protected LettClient $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->lett = new Lett($this->client = new LettClient(
            'login_key',
            'project_key'
        ));
    }

    /** @test */
    public function is_will_not_crash_if_let_returns_error_bad_response_exception(): void
    {
        $this->lett = new Lett($this->client = new \Lett\Http\Client(
            'login_key',
            'project_key'
        ));

        //
        $this->app['config']['lett.environments'] = ['testing'];

        $this->client->setGuzzleHttpClient(new Client([
            'handler' => MockHandler::createWithMiddleware([
                new Response(500, [], '{}'),
            ]),
        ]));

        $this->assertInstanceOf(get_class(new \stdClass()), $this->lett->handle(
            new Exception('is_will_not_crash_if_let_returns_error_bad_response_exception')
        ));
    }

    /** @test */
    public function is_will_not_crash_if_let_returns_normal_exception(): void
    {
        $this->lett = new Lett($this->client = new \Lett\Http\Client(
            'login_key',
            'project_key'
        ));

        //
        $this->app['config']['let.environments'] = ['testing'];

        $this->client->setGuzzleHttpClient(new Client([
            'handler' => MockHandler::createWithMiddleware([
                new \Exception(),
            ]),
        ]));

        $this->assertFalse($this->lett->handle(new Exception('is_will_not_crash_if_let_returns_normal_exception')));
    }

    /** @test */
    public function it_can_skip_exceptions_based_on_class(): void
    {
        $this->app['config']['lett.except'] = [];

        $this->assertFalse($this->lett->isSkipException(NotFoundHttpException::class));

        $this->app['config']['lett.except'] = [
            NotFoundHttpException::class,
        ];

        $this->assertTrue($this->lett->isSkipException(NotFoundHttpException::class));
    }

    /** @test */
    public function it_can_skip_exceptions_based_on_environment(): void
    {
        $this->app['config']['lett.environments'] = [];

        $this->assertTrue($this->lett->isSkipEnvironment());

        $this->app['config']['lett.environments'] = ['production'];

        $this->assertTrue($this->lett->isSkipEnvironment());

        $this->app['config']['lett.environments'] = ['testing'];

        $this->assertFalse($this->lett->isSkipEnvironment());
    }

    /** @test */
    public function it_will_return_false_for_sleeping_cache_exception_if_disabled(): void
    {
        $this->app['config']['lett.sleep'] = 0;

        $this->assertFalse($this->lett->isSleepingException([]));
    }

    /** @test */
    public function it_can_check_if_is_a_sleeping_cache_exception(): void
    {
        $data = ['host' => 'localhost', 'method' => 'GET',
            'exception' => 'it_can_check_if_is_a_sleeping_cache_exception',
            'line'      => 2, 'file' => '/tmp/lett/tests/lettTest.php', 'class' => 'Exception', ];

        Carbon::setTestNow('2019-10-12 13:30:00');

        $this->assertFalse($this->lett->isSleepingException($data));

        Carbon::setTestNow('2019-10-12 13:30:00');

        $this->lett->addExceptionToSleep($data);

        $this->assertTrue($this->lett->isSleepingException($data));

        Carbon::setTestNow('2019-10-12 13:31:00');

        $this->assertTrue($this->lett->isSleepingException($data));

        Carbon::setTestNow('2019-10-12 13:31:01');

        $this->assertFalse($this->lett->isSleepingException($data));
    }

    /** @test */
    public function it_can_get_formatted_exception_data(): void
    {
        $data = $this->lett->getExceptionData(new Exception(
            'it_can_get_formatted_exception_data'
        ));

        $this->assertSame('testing', $data['environment']);
        $this->assertSame('localhost', $data['host']);
        $this->assertSame('GET', $data['method']);
        $this->assertSame('http://localhost', $data['fullUrl']);
        $this->assertSame('it_can_get_formatted_exception_data', $data['exception']);

        $this->assertCount(13, $data);
    }

    /** @test */
    public function it_filters_the_data_based_on_the_configuration(): void
    {
        $this->assertContains('*password*', $this->app['config']['lett.blacklist']);

        $data = [
            'password'      => 'testing',
            'not_password'  => 'testing',
            'not_password2' => [
                'password' => 'testing',
            ],
            'not_password_3' => [
                'nah' => [
                    'password' => 'testing',
                ],
            ],
            'Password' => 'testing',
        ];

        $this->assertContains('***', $this->lett->filterVariables($data));
    }

    /** @test */
    public function it_can_report_an_exception_to_lett(): void
    {
        $this->app['config']['lett.environments'] = ['testing'];

        $this->let->handle(new Exception('it_can_report_an_exception_to_lett'));

        $this->client->assertRequestsSent(1);
    }
}
