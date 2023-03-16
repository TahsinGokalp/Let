<?php

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TahsinGokalp\Lett\Fakes\LettFake;
use TahsinGokalp\Lett\Tests\Mocks\LettClient;

it('is_will_not_crash_if_let_returns_error_bad_response_exception', function () {
    $lett = new LettFake($client = new LettClient(
        'login_key',
        'project_key'
    ));

    config()->set('lett.environments', ['testing']);

    $client->setGuzzleHttpClient(new Client([
        'handler' => MockHandler::createWithMiddleware([
            new Response(500, [], '{}'),
        ]),
    ]));

    expect($lett->handle(
        new Exception('is_will_not_crash_if_let_returns_error_bad_response_exception')
    )->getStatusCode())->toBe(200);
});

it('is_will_not_crash_if_let_returns_normal_exception', function () {
    $lett = new LettFake($client = new LettClient(
        'login_key',
        'project_key'
    ));

    config()->set('lett.environments', ['testing']);

    $client->setGuzzleHttpClient(new Client([
        'handler' => MockHandler::createWithMiddleware([
            new Exception,
        ]),
    ]));

    expect($lett->handle(
        new Exception('is_will_not_crash_if_let_returns_normal_exception')
    )->getStatusCode())->toBe(200);
});

it('it_can_skip_exceptions_based_on_class', function () {
    $lett = new LettFake(new LettClient(
        'login_key',
        'project_key'
    ));

    config()->set('lett.except', []);

    expect($lett->isSkipException(NotFoundHttpException::class))->toBe(false);

    config()->set('lett.except', [NotFoundHttpException::class]);

    expect($lett->isSkipException(NotFoundHttpException::class))->toBe(true);
});

it('it_can_skip_exceptions_based_on_environment', function () {
    $lett = new LettFake(new LettClient(
        'login_key',
        'project_key'
    ));

    config()->set('lett.environments', []);

    expect($lett->isSkipEnvironment())->toBe(true);

    config()->set('lett.environments', ['production']);

    expect($lett->isSkipEnvironment())->toBe(true);

    config()->set('lett.environments', ['testing']);

    expect($lett->isSkipEnvironment())->toBe(false);
});

it('it_will_return_false_for_sleeping_cache_exception_if_disabled', function () {
    $lett = new LettFake(new LettClient(
        'login_key',
        'project_key'
    ));

    config()->set('lett.sleep', 0);

    expect($lett->isSleepingException([]))->toBe(false);
});

it('it_can_check_if_is_a_sleeping_cache_exception', function () {
    $lett = new LettFake(new LettClient(
        'login_key',
        'project_key'
    ));

    $data = ['host' => 'localhost', 'method' => 'GET',
        'exception' => 'it_can_check_if_is_a_sleeping_cache_exception',
        'line' => 2, 'file' => '/tmp/lett/tests/lettTest.php', 'class' => 'Exception', ];

    Carbon::setTestNow('2019-10-12 13:30:00');

    expect($lett->isSleepingException($data))->toBe(false);

    Carbon::setTestNow('2019-10-12 13:30:00');

    $lett->addExceptionToSleep($data);

    expect($lett->isSleepingException($data))->toBe(true);

    Carbon::setTestNow('2019-10-12 13:31:00');

    expect($lett->isSleepingException($data))->toBe(true);

    Carbon::setTestNow('2019-10-12 13:31:01');

    expect($lett->isSleepingException($data))->toBe(false);
});

it('it_can_get_formatted_exception_data', function () {
    $lett = new LettFake(new LettClient(
        'login_key',
        'project_key'
    ));

    $data = $lett->getExceptionData(new Exception(
        'it_can_get_formatted_exception_data'
    ));

    expect($data['environment'])->toBe('testing')
        ->and($data['host'])->toBe('localhost')
        ->and($data['method'])->toBe('GET')
        ->and($data['fullUrl'])->toBe('http://localhost')
        ->and($data['exception'])->toBe('it_can_get_formatted_exception_data');
});

it('it_filters_the_data_based_on_the_configuration', function () {
    $lett = new LettFake(new LettClient(
        'login_key',
        'project_key'
    ));

    expect(config()->get('lett.blacklist'))->toContain('*password*');

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

    expect($lett->filterVariables($data))->toContain('***');
});

it('it_can_report_an_exception_to_lett', function () {
    $lett = new LettFake(new LettClient(
        'login_key',
        'project_key'
    ));

    config()->set('lett.environments', ['testing']);

    $lett->handle(new Exception('it_can_report_an_exception_to_lett'));

    expect(count($lett->requestsSent()))->toBe(1);
});
