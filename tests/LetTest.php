<?php

namespace Let\Tests;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Let\Tests\Mocks\LetClient;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TahsinGokalp\Let;

class LetTest extends TestCase
{
    /** @var Let */
    protected $let;

    /** @var Mocks\LetClient */
    protected $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->let = new Let($this->client = new LetClient(
            'login_key',
            'project_key'
        ));
    }

    /** @test */
    public function is_will_not_crash_if_let_returns_error_bad_response_exception()
    {
        $this->let = new Let($this->client = new \Let\Http\Client(
            'login_key',
            'project_key'
        ));

        //
        $this->app['config']['let.environments'] = ['testing'];

        $this->client->setGuzzleHttpClient(new Client([
            'handler' => MockHandler::createWithMiddleware([
                new Response(500, [], '{}'),
            ]),
        ]));

        $this->assertInstanceOf(get_class(new \stdClass()), $this->let->handle(new Exception('is_will_not_crash_if_let_returns_error_bad_response_exception')));
    }

    /** @test */
    public function is_will_not_crash_if_let_returns_normal_exception()
    {
        $this->let = new Let($this->client = new \Let\Http\Client(
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

        $this->assertFalse($this->let->handle(new Exception('is_will_not_crash_if_let_returns_normal_exception')));
    }

    /** @test */
    public function it_can_skip_exceptions_based_on_class()
    {
        $this->app['config']['let.except'] = [];

        $this->assertFalse($this->let->isSkipException(NotFoundHttpException::class));

        $this->app['config']['let.except'] = [
            NotFoundHttpException::class,
        ];

        $this->assertTrue($this->let->isSkipException(NotFoundHttpException::class));
    }

    /** @test */
    public function it_can_skip_exceptions_based_on_environment()
    {
        $this->app['config']['let.environments'] = [];

        $this->assertTrue($this->let->isSkipEnvironment());

        $this->app['config']['let.environments'] = ['production'];

        $this->assertTrue($this->let->isSkipEnvironment());

        $this->app['config']['let.environments'] = ['testing'];

        $this->assertFalse($this->let->isSkipEnvironment());
    }

    /** @test */
    public function it_will_return_false_for_sleeping_cache_exception_if_disabled()
    {
        $this->app['config']['let.sleep'] = 0;

        $this->assertFalse($this->let->isSleepingException([]));
    }

    /** @test */
    public function it_can_check_if_is_a_sleeping_cache_exception()
    {
        $data = ['host' => 'localhost', 'method' => 'GET', 'exception' => 'it_can_check_if_is_a_sleeping_cache_exception', 'line' => 2, 'file' => '/tmp/let/tests/letTest.php', 'class' => 'Exception'];

        Carbon::setTestNow('2019-10-12 13:30:00');

        $this->assertFalse($this->let->isSleepingException($data));

        Carbon::setTestNow('2019-10-12 13:30:00');

        $this->let->addExceptionToSleep($data);

        $this->assertTrue($this->let->isSleepingException($data));

        Carbon::setTestNow('2019-10-12 13:31:00');

        $this->assertTrue($this->let->isSleepingException($data));

        Carbon::setTestNow('2019-10-12 13:31:01');

        $this->assertFalse($this->let->isSleepingException($data));
    }

    /** @test */
    public function it_can_get_formatted_exception_data()
    {
        $data = $this->let->getExceptionData(new Exception(
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
    public function it_filters_the_data_based_on_the_configuration()
    {
        $this->assertContains('*password*', $this->app['config']['let.blacklist']);

        $data = [
            'password' => 'testing',
            'not_password' => 'testing',
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

        $this->assertContains('***', $this->let->filterVariables($data));
    }

    /** @test */
    public function it_can_report_an_exception_to_let()
    {
        $this->app['config']['let.environments'] = ['testing'];

        $this->let->handle(new Exception('it_can_report_an_exception_to_let'));

        $this->client->assertRequestsSent(1);
    }
}
