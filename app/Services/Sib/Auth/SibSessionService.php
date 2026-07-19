<?php

namespace App\Services\Sib\Auth;

use App\Data\Sib\Auth\AuthToken;

final class SibSessionService
{
    public function __construct(
        private readonly SibLoginService $loginService,
        private readonly SibRoleService $roleService,
    ) {
    }

    public function loginAndSetRole(
        string $username,
        string $password,
        int $roleUserId,
    ): AuthToken {
        $loginToken = $this->loginService->login($username, $password);

        return $this->roleService->setRole($loginToken->jwt, $roleUserId);
    }
}
