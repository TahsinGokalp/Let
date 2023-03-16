<?php

use TahsinGokalp\Lett\Lett;
use TahsinGokalp\Lett\Tests\Mocks\CustomerUser;
use TahsinGokalp\Lett\Tests\Mocks\CustomerUserWithToLett;
use TahsinGokalp\Lett\Tests\Mocks\LettClient;

it('it_return_custom_user', function () {
    $lett = new Lett(new LettClient(
        'login_key',
        'project_key'
    ));

    $this->actingAs((new CustomerUser)->forceFill([
        'id' => 1,
        'username' => 'username',
        'password' => 'password',
        'email' => 'email',
    ]));

    expect($lett->getUser())->toBe(['id' => 1, 'username' => 'username',
        'password' => 'password', 'email' => 'email', ]);
});

it('it_return_custom_user_with_to_lett', function () {
    $lett = new Lett(new LettClient(
        'login_key',
        'project_key'
    ));

    $this->actingAs((new CustomerUserWithToLett)->forceFill([
        'id' => 1,
        'username' => 'username',
        'password' => 'password',
        'email' => 'email',
    ]));

    expect($lett->getUser())->toBe(['username' => 'username', 'email' => 'email']);
});

it('it_returns_nothing_for_ghost', function () {
    $lett = new Lett(new LettClient(
        'login_key',
        'project_key'
    ));

    expect($lett->getUser())->toBeNull();
});
