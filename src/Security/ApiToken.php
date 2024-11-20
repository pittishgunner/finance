<?php

namespace App\Security;

final class ApiToken
{
    public function __construct(
        private string $user,
        private string $token
    ) {
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
