<?php

namespace Lett\Tests;

use Illuminate\Foundation\Auth\User as AuthUser;
use Lett\Lett;
use Lett\Tests\Mocks\LettClient;

class UserTest extends TestCase
{
    /** @var Mocks\LettClient */
    protected LettClient $client;

    /** @var Lett */
    protected Lett $lett;

    public function setUp(): void
    {
        parent::setUp();

        $this->lett = new Lett($this->client = new LettClient(
            'login_key',
            'project_key'
        ));
    }

    /** @test */
    public function it_return_custom_user(): void
    {
        $this->actingAs((new CustomerUser())->forceFill([
            'id' => 1,
            'username' => 'username',
            'password' => 'password',
            'email' => 'email',
        ]));

        $this->assertSame(['id' => 1, 'username' => 'username', 'password' => 'password', 'email' => 'email'], $this->lett->getUser());
    }

    /** @test */
    public function it_return_custom_user_with_to_lett(): void
    {
        $this->actingAs((new CustomerUserWithToLet())->forceFill([
            'id' => 1,
            'username' => 'username',
            'password' => 'password',
            'email' => 'email',
        ]));

        $this->assertSame(['username' => 'username', 'email' => 'email'], $this->lett->getUser());
    }

    /** @test */
    public function it_returns_nothing_for_ghost(): void
    {
        $this->assertSame(null, $this->lett->getUser());
    }
}

class CustomerUser extends AuthUser
{
    protected $guarded = [];
}

class CustomerUserWithToLet extends CustomerUser implements \Lett\Concerns\Lettable
{
    public function toLett()
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
        ];
    }
}
