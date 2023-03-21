<?php

use Illuminate\Support\Facades\Event;
use TahsinGokalp\Lett\Events\ApiKeyNotFound;
use TahsinGokalp\Lett\Events\EnvironmentNotFound;
use TahsinGokalp\Lett\Events\FoundApiKey;
use TahsinGokalp\Lett\Events\FoundEnvironment;
use TahsinGokalp\Lett\Events\FoundProjectKey;
use TahsinGokalp\Lett\Events\ProjectKeyNotFound;
use TahsinGokalp\Lett\Tests\Fakes\LettFake;
use TahsinGokalp\Lett\Tests\Mocks\LettClient;

beforeEach(static function () {
    Event::fake();
});

it('it_detects_if_the_login_key_is_set', static function () {
    config()->set('lett.login_key', '');

    test()->artisan('lett:doctor')->assertExitCode(0);

    Event::assertDispatched(ApiKeyNotFound::class);

    config()->set('lett.login_key', 'test');

    test()->artisan('lett:doctor')->assertExitCode(0);

    Event::assertDispatched(FoundApiKey::class);
});

it('it_detects_if_the_project_key_is_set', static function () {
    config()->set('lett.project_key', '');

    test()->artisan('lett:doctor')->assertExitCode(0);

    Event::assertDispatched(ProjectKeyNotFound::class);

    config()->set('lett.project_key', 'test');

    test()->artisan('lett:doctor')->assertExitCode(0);

    Event::assertDispatched(FoundProjectKey::class);
});

it('it_detects_that_its_running_in_the_correct_environment', static function () {
    config()->set('app.env', 'production');
    config()->set('lett.environments', []);

    test()->artisan('lett:doctor')->assertExitCode(0);

    Event::assertDispatched(EnvironmentNotFound::class);

    config()->set('lett.environments', ['production']);

    test()->artisan('lett:doctor')->assertExitCode(0);

    Event::assertDispatched(FoundEnvironment::class);
});

it('it_detects_that_it_fails_to_send_to_lett', function () {
    $this->artisan('lett:test')->run();
    $this->artisan('lett:test')
        ->expectsOutput(__('Failed to send exception to lett'))
        ->assertExitCode(0);

    config()->set('lett.environments', ['testing']);

    $lett = new LettFake(new LettClient(
        'login_key',
        'project_key'
    ));

    \TahsinGokalp\Lett\Facades\Lett::swap($lett);

    $this->artisan('lett:test')
        ->expectsOutput(__('Sent exception to lett'))
        ->assertExitCode(0);
});
