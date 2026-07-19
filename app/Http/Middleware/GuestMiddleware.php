<?php

namespace App\Http\Middleware;

use App\Services\Sib\User\SibUserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class GuestMiddleware
{
    public function __construct(protected readonly SibUserService $userService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = Auth::user()?->token;

        if (blank($token)) {
            return $next($request);
        }

        try {
            $this->userService->getUserInfo($token);

            // Fully authenticated with role set.
            return redirect()->route('dashboard');
        } catch (Throwable $e) {
            // Token exists but active role is not set.
            if ($this->isRoleNotSetException($e)) {
                return redirect()->route('get.role');
            }

            // If token is invalid or expired, treat as guest.
            return $next($request);
        }
    }

    private function isRoleNotSetException(Throwable $e): bool
    {
        return str_contains(get_class($e), 'NotFoundException')
            || str_contains($e->getMessage(), 'NotFound');
    }
}
