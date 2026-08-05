<?php

namespace App\Http\Middleware;

use App\Services\Sib\User\SibAdminUserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RolePendingMiddleware
{
    public function __construct(protected readonly SibAdminUserService $userService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $userInfo = $this->userService->getUserInfo();

            // If userInfo is successfully loaded, set it in request attributes.
            $request->setSibAdminUser($userInfo);
            
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
