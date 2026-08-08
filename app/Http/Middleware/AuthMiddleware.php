<?php

namespace App\Http\Middleware;

use App\Services\Sib\User\SibAdminUserService;
use App\Services\Sib\User\SibUserSearchService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

readonly class AuthMiddleware
{
    public function __construct(
        protected SibAdminUserService $userService,
        protected SibUserSearchService $searchService,
    )
    {
    }

    public function handle(Request $request, Closure $next): Response
    {

        try {
            $userInfo = $this->userService->getUserInfo();

            // If userInfo is successfully loaded, set it in request attributes.
            $request->setSibAdminUser($userInfo);
            $cacheKey="service-group-{$userInfo->networkId}";
            if (!Cache::has($cacheKey)){
                Cache::put($cacheKey,$this->searchService->serviceGroup(null,$userInfo->networkId),24*60*60);
            }
        } catch (Throwable $e) {
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
