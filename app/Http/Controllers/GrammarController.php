<?php

namespace App\Http\Controllers;

use App\Models\GrammarPoint;
use Illuminate\Http\Request;

class GrammarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = GrammarPoint::with(['lesson']);

        // Filter by lesson if provided
        if ($request->filled('lesson_id')) {
            $query->where('lesson_id', $request->lesson_id);
        }

        // Filter by JLPT level if provided
        if ($request->filled('jlpt_level')) {
            $query->where('jlpt_level', $request->jlpt_level);
        }

        // Search by name if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_english', 'like', "%{$search}%")
                  ->orWhere('name_japanese', 'like', "%{$search}%")
                  ->orWhere('pattern', 'like', "%{$search}%");
            });
        }

        $grammarPoints = $query->orderBy('lesson_id')->orderBy('name_english')->paginate(20);

        // Get unique lessons and JLPT levels for filters
        $lessons = \App\Models\Lesson::orderBy('chapter')->get();
        $jlptLevels = GrammarPoint::select('jlpt_level')->distinct()->whereNotNull('jlpt_level')->orderBy('jlpt_level')->pluck('jlpt_level');

        return view('grammar.index', compact('grammarPoints', 'lessons', 'jlptLevels'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $grammarPoint = GrammarPoint::with(['lesson', 'questions'])->findOrFail($id);
        return view('grammar.show', compact('grammarPoint'));
    }
}
