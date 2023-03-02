<?php

namespace Lett\Tests;

use Illuminate\Foundation\Auth\User as AuthUser;
use let\Tests\Mocks\LettClient;
use TahsinGokalp\Lett;

class UserTest extends TestCase
{
    /** @var Mocks\LettClient */
    protected $client;

    /** @var Lett */
    protected $lett;

    public function setUp(): void
    {
        parent::setUp();

        $this->lett = new Lett($this->client = new LettClient(
            'login_key',
            'project_key'
        ));
    }

    /** @test */
    public function it_return_custom_user()
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
    public function it_return_custom_user_with_to_lett()
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
    public function it_returns_nothing_for_ghost()
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
