<?php

namespace App\Http\Middleware;

use App\Services\Metrics\MetricsServiceInterface;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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
                $this->resolveStatusCode($e),
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

    private function resolveStatusCode(Throwable $e): int
    {
        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }
        if ($e instanceof AuthorizationException) {
            return $e->status() ?? 403;
        }
        if ($e instanceof ValidationException) {
            return $e->status;
        }
        if ($e instanceof HttpResponseException) {
            return $e->getResponse()->getStatusCode();
        }
        if ($e instanceof ModelNotFoundException) {
            return 404;
        }

        return 500;
    }
}
