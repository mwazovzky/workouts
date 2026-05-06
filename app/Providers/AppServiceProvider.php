<?php

namespace App\Providers;

use App\Http\Resources\UserResource;
use App\Services\Metrics\MetricsService;
use App\Services\Metrics\MetricsServiceInterface;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Prometheus\Storage\Adapter;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Workout\WorkoutServiceInterface::class,
            \App\Services\Workout\WorkoutService::class
        );

        $this->app->bind(Adapter::class, function () {
            if (app()->environment('testing')) {
                return new InMemory;
            }

            // @codeCoverageIgnoreStart
            return new Redis([
                'host' => config('metrics.redis.host'),
                'port' => config('metrics.redis.port'),
                'password' => config('metrics.redis.password'),
                'database' => config('metrics.redis.database'),
            ]);
            // @codeCoverageIgnoreEnd
        });

        $this->app->bind(MetricsServiceInterface::class, MetricsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Relation::morphMap([
            'workout_template' => \App\Models\WorkoutTemplate::class,
            'workout' => \App\Models\Workout::class,
            'exercise' => \App\Models\Exercise::class,
            'category' => \App\Models\Category::class,
            'equipment' => \App\Models\Equipment::class,
            'program' => \App\Models\Program::class,
        ]);

        // Share authenticated user info with Inertia pages
        Inertia::share('auth.user', function (Request $request) {
            $user = $request->user();
            if (! $user) {
                return null;
            }

            return (new UserResource($user))->resolve();
        });
    }
}
