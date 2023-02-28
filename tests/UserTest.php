<?php

namespace Let\Tests;

use Illuminate\Foundation\Auth\User as AuthUser;
use let\Tests\Mocks\LetClient;
use TahsinGokalp\Let;

class UserTest extends TestCase
{
    /** @var Mocks\LetClient */
    protected $client;

    /** @var Let */
    protected $let;

    public function setUp(): void
    {
        parent::setUp();

        $this->let = new Let($this->client = new LetClient(
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

        $this->assertSame(['id' => 1, 'username' => 'username', 'password' => 'password', 'email' => 'email'], $this->let->getUser());
    }

    /** @test */
    public function it_return_custom_user_with_to_let()
    {
        $this->actingAs((new CustomerUserWithToLet())->forceFill([
            'id' => 1,
            'username' => 'username',
            'password' => 'password',
            'email' => 'email',
        ]));

        $this->assertSame(['username' => 'username', 'email' => 'email'], $this->let->getUser());
    }

    /** @test */
    public function it_returns_nothing_for_ghost()
    {
        $this->assertSame(null, $this->let->getUser());
    }
}

class CustomerUser extends AuthUser
{
    protected $guarded = [];
}

class CustomerUserWithToLet extends CustomerUser implements \let\Concerns\Letable
{
    public function toLet()
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
        ];
    }
}
