<?php

namespace Tests\Feature\Metrics;

use App\Models\Program;
use App\Models\User;
use App\Models\Workout;
use App\Services\Metrics\MetricsServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BusinessMetricsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function registration_increments_user_registered_counter(): void
    {
        $this->mock(MetricsServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('incrementUserRegistered')->once();
            $mock->shouldIgnoreMissing();
        });

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
    }

    #[Test]
    public function completing_workout_increments_counter_and_updates_gauge(): void
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'status' => 'in_progress']);

        $this->mock(MetricsServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('incrementWorkoutCompleted')->once();
            $mock->shouldReceive('setActiveWorkouts')->once()->with(\Mockery::type('int'));
            $mock->shouldIgnoreMissing();
        });

        $this->actingAs($user)->postJson("/api/v1/workouts/{$workout->id}/complete");
    }

    #[Test]
    public function enrolling_in_program_increments_counter(): void
    {
        $user = User::factory()->create();
        $program = Program::factory()->create();

        $this->mock(MetricsServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('incrementProgramEnrolled')->once();
            $mock->shouldIgnoreMissing();
        });

        $this->actingAs($user)->postJson("/api/v1/programs/{$program->id}/enroll");
    }
}
