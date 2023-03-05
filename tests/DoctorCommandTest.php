<?php

use TahsinGokalp\Lett\Fakes\LettFake;
use TahsinGokalp\Lett\Tests\Mocks\LettClient;

it('it_detects_if_the_login_key_is_set', function () {
    config()->set('lett.login_key', '');

    test()->artisan('lett:doctor')->expectsOutput('✗ [Lett] Could not find your login key, set this in your .env')->assertExitCode(0);

    config()->set('lett.login_key', 'test');

    test()->artisan('lett:doctor')->expectsOutput('✓ [Lett] Found login key')->assertExitCode(0);
});

it('it_detects_if_the_project_key_is_set', function () {
    config()->set('lett.project_key', '');

    test()->artisan('lett:doctor')->expectsOutput('✗ [Lett] Could not find your project key, set this in your .env')->assertExitCode(0);

    config()->set('lett.project_key', 'test');

    test()->artisan('lett:doctor')->expectsOutput('✓ [Lett] Found project key')->assertExitCode(0);
});

it('it_detects_that_its_running_in_the_correct_environment', function () {
    config()->set('app.env', 'production');
    config()->set('lett.environments', []);

    test()->artisan('lett:doctor')->expectsOutput('✗ [Lett] Environment ('.config('app.env').') not allowed to send errors to Lett, set this in your config')->assertExitCode(0);

    config()->set('lett.environments', ['production']);

    test()->artisan('lett:doctor')->expectsOutput('✓ [Lett] Correct environment found ('.config('app.env').')')->assertExitCode(0);
});

it('it_detects_that_it_fails_to_send_to_lett', function () {
    $this->artisan('lett:test')->run();
    $this->artisan('lett:test')
        ->expectsOutput('✗ [Lett] Failed to send exception to lett')
        ->assertExitCode(0);

    config()->set('lett.environments', ['testing']);

    $lett = new LettFake($client = new LettClient(
        'login_key',
        'project_key'
    ));

    \TahsinGokalp\Lett\Facade::swap($lett);

    $lett = app('lett');

    $this->artisan('lett:test')
        ->expectsOutput('✓ [Lett] Sent exception to lett with ID: '.LettClient::RESPONSE_ID)
        ->assertExitCode(0);
});
