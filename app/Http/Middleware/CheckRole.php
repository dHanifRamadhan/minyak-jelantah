<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class CheckRole {
    public function handle($request, Closure $next, $role) {
        $users = DB::table('users')->where('email', $request->user()->email)->first();
        if ($users && $users->role === $role) {
            return $next($request);
        }
        return response()->json([
            'code' => 403,
            'message' => 'Unauthorized'
        ], 403);
    }
}