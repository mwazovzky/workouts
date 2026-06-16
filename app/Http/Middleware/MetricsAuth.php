<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MetricsAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('metrics.token');

        if ($token !== null && $request->bearerToken() !== $token) {
            abort(401);
        }

        return $next($request);
    }
}
