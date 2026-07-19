<?php

namespace App\Console\Commands;

use App\Exceptions\SibApiException;
use App\Services\Sib\Auth\SibLoginService;
use App\Services\Sib\Auth\SibRoleService;
use Illuminate\Console\Command;

final class SibLoginTest extends Command
{
    protected $signature = 'sib:login-test
                            {--role= : SIB role-user ID}';

    protected $description = 'Test SIB login and role selection';

    public function handle(
        SibLoginService $loginService,
        SibRoleService $roleService,
    ): int {
        $username = trim((string) $this->ask('Username'));
        $password = (string) $this->secret('Password');

        if ($username === '' || $password === '') {
            $this->error('Username and password are required.');

            return self::INVALID;
        }

        $roleUserId = $this->resolveRoleUserId();

        if ($roleUserId === null) {
            return self::INVALID;
        }

        try {
            $token = $loginService->login($username, $password);

            $this->info('Login successful.');
            $this->line("Token type: {$token->type}");
            $this->line(
                "Expires at: {$token->expiresAt->toIso8601String()}"
            );

            $roleService->setRole(
                accessToken: $token->jwt,
                roleUserId: $roleUserId,
            );

            $this->info('Role selected successfully.');

            // Use $token->jwt for subsequent SIB requests.
            // Never print or log the JWT.
            return self::SUCCESS;
        } catch (SibApiException $exception) {
            $this->renderSibException($exception);

            return self::FAILURE;
        }
    }

    private function resolveRoleUserId(): ?int
    {
        $roleUserId = $this->option('role');

        if ($roleUserId === null) {
            $roleUserId = $this->ask('Role user ID');
        }

        $roleUserId = filter_var(
            $roleUserId,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );

        if ($roleUserId === false) {
            $this->error('Role user ID must be a positive integer.');

            return null;
        }

        return $roleUserId;
    }

    private function renderSibException(SibApiException $exception): void
    {
        $this->error($exception->getMessage());

        if ($exception->httpStatus !== null) {
            $this->line("HTTP Status: {$exception->httpStatus}");
        }

        if ($exception->sibCode !== null) {
            $this->line("SIB Code: {$exception->sibCode}");
        }

        if ($exception->traceId !== null) {
            $this->line("Trace ID: {$exception->traceId}");
        }

        logger()->warning('SIB authentication flow failed.', [
            'http_status' => $exception->httpStatus,
            'sib_code' => $exception->sibCode,
            'trace_id' => $exception->traceId,
            'message' => $exception->getMessage(),
        ]);
    }
}
