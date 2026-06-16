<?php

namespace Tests\Unit\Services\Metrics;

use App\Services\Metrics\MetricsService;
use PHPUnit\Framework\Attributes\Test;
use Prometheus\Storage\Adapter;
use Prometheus\Storage\InMemory;
use RuntimeException;
use Tests\TestCase;

class MetricsServiceTest extends TestCase
{
    #[Test]
    public function all_increment_methods_succeed_with_working_storage(): void
    {
        $service = new MetricsService(new InMemory);

        $service->incrementUserRegistered();
        $service->incrementWorkoutCompleted();
        $service->incrementProgramEnrolled();
        $service->setActiveWorkouts(3);
        $service->recordHttpRequest('GET', 'dashboard', 200, 0.05);

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function all_methods_absorb_storage_exceptions_silently(): void
    {
        $storage = $this->createMock(Adapter::class);
        $storage->method('updateCounter')->willThrowException(new RuntimeException('Redis down'));
        $storage->method('updateGauge')->willThrowException(new RuntimeException('Redis down'));
        $storage->method('updateHistogram')->willThrowException(new RuntimeException('Redis down'));
        $storage->method('collect')->willThrowException(new RuntimeException('Redis down'));

        $service = new MetricsService($storage);

        $service->incrementUserRegistered();
        $service->incrementWorkoutCompleted();
        $service->incrementProgramEnrolled();
        $service->setActiveWorkouts(5);
        $service->recordHttpRequest('POST', 'workouts.store', 201, 0.12);

        $this->expectNotToPerformAssertions();
    }
}
