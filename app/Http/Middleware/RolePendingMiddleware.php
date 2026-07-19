<?php

namespace App\Http\Middleware;

use App\Services\Sib\User\SibUserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RolePendingMiddleware
{
    public function __construct(protected readonly SibUserService $userService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = Auth::user()?->token;

        if (blank($token)) {
            return redirect()->route('login');
        }

        try {
            $this->userService->getUserInfo($token);

            // Already has an active role, send to dashboard.
            return redirect()->route('dashboard');
        } catch (Throwable $e) {
            // Allow access to role selection page only if they get the NotFoundException.
            if (str_contains(get_class($e), 'NotFoundException') || str_contains($e->getMessage(), 'NotFound')) {
                return $next($request);
            }

            return redirect()->route('login');
        }
    }
}
