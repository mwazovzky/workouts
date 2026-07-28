<?php

use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\EquipmentController as AdminEquipmentController;
use App\Http\Controllers\Api\Admin\ExerciseController as AdminExerciseController;
use App\Http\Controllers\Api\Admin\ProgramController as AdminProgramController;
use App\Http\Controllers\Api\Admin\WorkoutTemplateController as AdminWorkoutTemplateController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExerciseController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\WorkoutController;
use App\Http\Controllers\Api\WorkoutTemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('tokens', [TokenController::class, 'store'])->name('tokens.store');

    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('tokens/current', [TokenController::class, 'destroy'])->name('tokens.destroy');

        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::patch('profile/locale', [ProfileController::class, 'updateLocale'])->name('profile.locale');
        Route::patch('profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme');
        Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::apiResource('programs', ProgramController::class)->only(['index', 'show']);
        Route::post('programs/{program}/enroll', [ProgramController::class, 'enroll'])->name('programs.enroll');

        Route::get('workout-templates/{id}', [WorkoutTemplateController::class, 'show'])->name('workout-templates.show');

        Route::get('exercises', [ExerciseController::class, 'index'])->name('exercises.index');

        Route::post('workouts/{workout}/complete', [WorkoutController::class, 'complete'])->name('workouts.complete');
        Route::post('workouts/{workout}/repeat', [WorkoutController::class, 'repeat'])->name('workouts.repeat');
        Route::patch('workouts/{workout}/save', [WorkoutController::class, 'save'])->name('workouts.save');
        Route::apiResource('workouts', WorkoutController::class)->except(['update']);

        Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
            Route::apiResource('equipment', AdminEquipmentController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('categories', AdminCategoryController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('exercises', AdminExerciseController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('workout-templates', AdminWorkoutTemplateController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('programs', AdminProgramController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
        });
    });
});
