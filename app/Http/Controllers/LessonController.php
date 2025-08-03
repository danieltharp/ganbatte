<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lessons = Lesson::orderBy('chapter')->get();
        return view('lessons.index', compact('lessons'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $lesson = Lesson::with(['vocabulary', 'grammarPoints', 'questions', 'worksheets'])
                       ->findOrFail($id);
        
        return view('lessons.show', compact('lesson'));
    }
}
