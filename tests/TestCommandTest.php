<?php

namespace Lett\Tests;

use Lett\Lett;
use Lett\Tests\Mocks\LettClient;

class TestCommandTest extends TestCase
{
    /** @test */
    public function it_detects_if_the_login_key_is_set(): void
    {
        $this->app['config']['lett.login_key'] = '';

        $this->artisan('lett:test')
            ->expectsOutput('✗ [Lett] Could not find your login key, set this in your .env')
            ->assertExitCode(0);

        $this->app['config']['lett.login_key'] = 'test';

        $this->artisan('lett:test')
            ->expectsOutput('✓ [Lett] Found login key')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_detects_if_the_project_key_is_set(): void
    {
        $this->app['config']['lett.project_key'] = '';

        $this->artisan('lett:test')
            ->expectsOutput('✗ [Lett] Could not find your project key, set this in your .env')
            ->assertExitCode(0);

        $this->app['config']['lett.project_key'] = 'test';

        $this->artisan('lett:test')
            ->expectsOutput('✓ [Lett] Found project key')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_detects_that_its_running_in_the_correct_environment(): void
    {
        $this->app['config']['app.env'] = 'production';
        $this->app['config']['lett.environments'] = [];

        $this->artisan('lett:test')
            ->expectsOutput('✗ [Lett] Environment (production) not allowed to send errors to let, set this in your config')
            ->assertExitCode(0);

        $this->app['config']['lett.environments'] = ['production'];

        $this->artisan('lett:test')
            ->expectsOutput('✓ [Lett] Correct environment found ('.config('app.env').')')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_detects_that_it_fails_to_send_to_lett(): void
    {
        $this->artisan('lett:test')
            ->expectsOutput('✗ [Lett] Failed to send exception to lett')
            ->assertExitCode(0);

        $this->app['config']['lett.environments'] = [
            'testing',
        ];
        $this->app['lett'] = new Lett($this->client = new LettClient(
            'login_key',
            'project_key'
        ));

        $this->artisan('lett:test')
            ->expectsOutput('✓ [Lett] Sent exception to lett with ID: '.LettClient::RESPONSE_ID)
            ->assertExitCode(0);

        $this->assertEquals(LettClient::RESPONSE_ID, $this->app['lett']->getLastExceptionId());
    }
}
