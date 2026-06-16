<?php

namespace App\Services\Metrics;

use Prometheus\CollectorRegistry;
use Prometheus\Storage\Adapter;
use Throwable;

class MetricsService implements MetricsServiceInterface
{
    private const NAMESPACE = 'app';

    private ?CollectorRegistry $registry = null;

    public function __construct(private readonly Adapter $storage) {}

    public function incrementUserRegistered(): void
    {
        $this->safeIncrement('user_registrations_total', 'Total user registrations');
    }

    public function incrementWorkoutCompleted(): void
    {
        $this->safeIncrement('workout_completed_total', 'Total completed workouts');
    }

    public function incrementProgramEnrolled(): void
    {
        $this->safeIncrement('program_enrolled_total', 'Total program enrollments');
    }

    public function setActiveWorkouts(int $count): void
    {
        try {
            $this->registry()
                ->getOrRegisterGauge(self::NAMESPACE, 'active_workouts', 'Current in-progress workouts')
                ->set($count);
        } catch (Throwable) {
        }
    }

    public function recordHttpRequest(string $method, string $route, int $statusCode, float $durationSeconds): void
    {
        try {
            $this->registry()
                ->getOrRegisterHistogram(
                    self::NAMESPACE,
                    'http_request_duration_seconds',
                    'HTTP request duration in seconds',
                    ['method', 'route', 'status_code'],
                    [0.01, 0.05, 0.1, 0.25, 0.5, 1.0, 2.5, 5.0]
                )
                ->observe($durationSeconds, [$method, $route, (string) $statusCode]);
        } catch (Throwable) {
        }
    }

    private function registry(): CollectorRegistry
    {
        if ($this->registry === null) {
            $this->registry = new CollectorRegistry($this->storage);
        }

        return $this->registry;
    }

    private function safeIncrement(string $name, string $help): void
    {
        try {
            $this->registry()
                ->getOrRegisterCounter(self::NAMESPACE, $name, $help)
                ->inc();
        } catch (Throwable) {
        }
    }
}
