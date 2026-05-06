<?php

namespace App\Http\Middleware;

use App\Services\Metrics\MetricsServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordHttpMetrics
{
    public function __construct(private readonly MetricsServiceInterface $metrics) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('metrics')) {
            return $next($request);
        }

        $start = microtime(true);

        $response = $next($request);

        $this->metrics->recordHttpRequest(
            $request->method(),
            $request->route()?->getName() ?? 'unknown',
            $response->getStatusCode(),
            microtime(true) - $start,
        );

        return $response;
    }
}
