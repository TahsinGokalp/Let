<?php

use TahsinGokalp\Lett\Tests\Fakes\LettFake;
use TahsinGokalp\Lett\Tests\Mocks\LettClient;

it('it_detects_if_the_login_key_is_set', function () {
    config()->set('lett.login_key', '');

    test()->artisan('lett:doctor')->expectsOutput(__('Could not find your API key, set this in your .env'))->assertExitCode(0);

    config()->set('lett.login_key', 'test');

    test()->artisan('lett:doctor')->expectsOutput(__('Found API key'))->assertExitCode(0);
});

it('it_detects_if_the_project_key_is_set', function () {
    config()->set('lett.project_key', '');

    test()->artisan('lett:doctor')->expectsOutput(__('Could not find your project key, set this in your .env'))->assertExitCode(0);

    config()->set('lett.project_key', 'test');

    test()->artisan('lett:doctor')->expectsOutput(__('Found project key'))->assertExitCode(0);
});

it('it_detects_that_its_running_in_the_correct_environment', function () {
    config()->set('app.env', 'production');
    config()->set('lett.environments', []);

    test()->artisan('lett:doctor')->expectsOutput(__('Environment () not allowed to send errors to Lett, set this in your config', ['environment' => config('app.env')]))
        ->assertExitCode(0);

    config()->set('lett.environments', ['production']);

    test()->artisan('lett:doctor')->expectsOutput(__('Correct environment found ()', ['environment' => config('app.env')]))->assertExitCode(0);
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
