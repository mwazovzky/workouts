<?php

namespace App\Services\Metrics;

interface MetricsServiceInterface
{
    public function incrementUserRegistered(): void;

    public function incrementWorkoutCompleted(): void;

    public function incrementProgramEnrolled(): void;

    public function setActiveWorkouts(int $count): void;

    public function recordHttpRequest(string $method, string $route, int $statusCode, float $durationSeconds): void;
}
