<?php

namespace App\Http\Middleware;

use App\Services\Metrics\MetricsServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordHttpMetrics
{
    public function __construct(private readonly MetricsServiceInterface $metrics) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('metrics')) {
            return $next($request);
        }

        $start = microtime(true);

        try {
            $response = $next($request);
        } catch (Throwable $e) {
            $this->metrics->recordHttpRequest(
                $request->method(),
                $request->route()?->getName() ?? 'unknown',
                500,
                microtime(true) - $start,
            );
            throw $e;
        }

        $this->metrics->recordHttpRequest(
            $request->method(),
            $request->route()?->getName() ?? 'unknown',
            $response->getStatusCode(),
            microtime(true) - $start,
        );

        return $response;
    }
}
