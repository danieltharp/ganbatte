<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exercises = Exercise::with(['lesson'])->orderBy('lesson_id')->orderBy('order_weight')->get();
        return view('exercises.index', compact('exercises'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $exercise = Exercise::with(['lesson'])->findOrFail($id);
        return view('exercises.show', compact('exercise'));
    }
}
