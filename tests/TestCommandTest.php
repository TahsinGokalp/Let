<?php

namespace Let\Tests;

use Let\Tests\Mocks\LetClient;
use TahsinGokalp\Let;

class TestCommandTest extends TestCase
{
    /** @test */
    public function it_detects_if_the_login_key_is_set()
    {
        $this->app['config']['let.login_key'] = '';

        $this->artisan('let:test')
            ->expectsOutput('✗ [let] Could not find your login key, set this in your .env')
            ->assertExitCode(0);

        $this->app['config']['let.login_key'] = 'test';

        $this->artisan('let:test')
            ->expectsOutput('✓ [let] Found login key')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_detects_if_the_project_key_is_set()
    {
        $this->app['config']['let.project_key'] = '';

        $this->artisan('let:test')
            ->expectsOutput('✗ [let] Could not find your project key, set this in your .env')
            ->assertExitCode(0);

        $this->app['config']['let.project_key'] = 'test';

        $this->artisan('let:test')
            ->expectsOutput('✓ [let] Found project key')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_detects_that_its_running_in_the_correct_environment()
    {
        $this->app['config']['app.env'] = 'production';
        $this->app['config']['let.environments'] = [];

        $this->artisan('let:test')
            ->expectsOutput('✗ [let] Environment (production) not allowed to send errors to let, set this in your config')
            ->assertExitCode(0);

        $this->app['config']['let.environments'] = ['production'];

        $this->artisan('let:test')
            ->expectsOutput('✓ [let] Correct environment found ('.config('app.env').')')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_detects_that_it_fails_to_send_to_let()
    {
        $this->artisan('let:test')
            ->expectsOutput('✗ [let] Failed to send exception to let')
            ->assertExitCode(0);

        $this->app['config']['let.environments'] = [
            'testing',
        ];
        $this->app['let'] = new Let($this->client = new LetClient(
            'login_key',
            'project_key'
        ));

        $this->artisan('let:test')
            ->expectsOutput('✓ [let] Sent exception to let with ID: '.LetClient::RESPONSE_ID)
            ->assertExitCode(0);

        $this->assertEquals(LetClient::RESPONSE_ID, $this->app['let']->getLastExceptionId());
    }
}
