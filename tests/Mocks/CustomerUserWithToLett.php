<?php

namespace TahsinGokalp\Lett\Tests\Mocks;

use TahsinGokalp\Lett\Concerns\Lettable;

class CustomerUserWithToLett extends CustomerUser implements Lettable
{
    public function toLett(): array
    {
        return [
            'username' => $this->username,
            'email'    => $this->email,
        ];
    }
}
