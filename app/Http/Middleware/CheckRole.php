<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (Auth::check() && in_array(Auth::users()->role, $roles))
        {
            return $next($request);
        }
        return response()->json([
            'code' => 403,
            'message' => 'Hak akses tidak di izinkan'
        ], 403);
    }
}
