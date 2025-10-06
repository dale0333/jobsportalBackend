<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifySystemKey
{
    public function handle(Request $request, Closure $next)
    {
        $systemKey = $request->header('X-System-Key');
        $validKey  = config('app.system_key');

        if ($systemKey !== $validKey) {
            return response()->json(['message' => 'Unauthorized. Invalid system key.'], 401);
        }

        return $next($request);
    }
}
