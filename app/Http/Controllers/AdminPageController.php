<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class AdminPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Index');
    }

    public function equipment(): Response
    {
        return Inertia::render('Admin/EquipmentIndex');
    }

    public function categories(): Response
    {
        return Inertia::render('Admin/CategoryIndex');
    }

    public function exercises(): Response
    {
        return Inertia::render('Admin/ExerciseIndex');
    }

    public function workoutTemplates(): Response
    {
        return Inertia::render('Admin/WorkoutTemplateIndex');
    }

    public function programs(): Response
    {
        return Inertia::render('Admin/ProgramIndex');
    }
}
