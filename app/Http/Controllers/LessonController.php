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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('lessons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|unique:lessons',
            'chapter' => 'required|integer|min:1|max:50',
            'title_english' => 'required|string|max:255',
            'title_japanese' => 'nullable|string|max:255',
            'title_furigana' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'difficulty' => 'nullable|in:beginner,elementary,intermediate,advanced',
            'estimated_time_minutes' => 'nullable|integer|min:1',
        ]);

        $lesson = Lesson::create($validated);
        return redirect()->route('lessons.show', $lesson)->with('success', 'Lesson created successfully!');
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $lesson = Lesson::findOrFail($id);
        return view('lessons.edit', compact('lesson'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $lesson = Lesson::findOrFail($id);
        
        $validated = $request->validate([
            'chapter' => 'required|integer|min:1|max:50',
            'title_english' => 'required|string|max:255',
            'title_japanese' => 'nullable|string|max:255',
            'title_furigana' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'difficulty' => 'nullable|in:beginner,elementary,intermediate,advanced',
            'estimated_time_minutes' => 'nullable|integer|min:1',
        ]);

        $lesson->update($validated);
        return redirect()->route('lessons.show', $lesson)->with('success', 'Lesson updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lesson = Lesson::findOrFail($id);
        $lesson->delete();
        return redirect()->route('lessons.index')->with('success', 'Lesson deleted successfully!');
    }
}
