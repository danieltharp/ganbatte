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
        $exercise = Exercise::with([
            'lesson.pages' => function ($query) {
                $query->orderBy('page_number');
            }
        ])->findOrFail($id);
        
        // Load questions based on question_ids
        $questions = [];
        if ($exercise->question_ids && count($exercise->question_ids) > 0) {
            $questions = \App\Models\Question::whereIn('id', $exercise->question_ids)
                ->get()
                ->sortBy(function ($question) use ($exercise) {
                    return array_search($question->id, $exercise->question_ids);
                })
                ->values();
        }
        
        return view('exercises.show', compact('exercise', 'questions'));
    }
}
