<?php

namespace App\Http\Middleware;

use App\Services\Sib\User\SibAdminUserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

readonly class AuthMiddleware
{
    public function __construct(protected SibAdminUserService $userService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {

        try {
            $userInfo = $this->userService->getUserInfo();

            // If userInfo is successfully loaded, set it in request attributes.
            $request->setSibAdminUser($userInfo);
        } catch (Throwable $e) {
            Auth::user()?->update([
                'role_code'=>null
            ]);
            // Check if exception indicates that the user profile/role is missing or not set.
            if ($this->isRoleNotSetException($e)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Role not selected.',
                        'redirect' => route('get.role'),
                    ], 403);
                }
                return redirect()->route('get.role');
            }
            return $this->unauthenticated($request);
        }

        return $next($request);
    }

    private function unauthenticated(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }
        return redirect()->route('login');
    }

    /**
     * Determine if the thrown exception is due to active role not being set on SIB.
     */
    private function isRoleNotSetException(Throwable $e): bool
    {
        // Matches NotFoundException class or similar API response message
        return str_contains(get_class($e), 'NotFoundException')
            || str_contains($e->getMessage(), 'NotFound');
    }
}
