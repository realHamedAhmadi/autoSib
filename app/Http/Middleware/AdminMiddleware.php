<?php

namespace App\Http\Middleware;

use App\Services\Sib\User\SibAdminUserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

readonly class AdminMiddleware
{
    public function __construct()
    {
    }

    public function handle(Request $request, Closure $next): Response
    {

        if (!Auth::check()){
            return $this->unauthenticated($request);
        }

        /*if (!Auth::user()->isAdmin()){
            return redirect()->route('dashboard');
        }*/

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
}
