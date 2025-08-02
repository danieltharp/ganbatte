<?php

namespace App\Http\Controllers;

use App\Models\Vocabulary;
use App\Models\Lesson;
use Illuminate\Http\Request;

class VocabularyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Vocabulary::with('lesson');
        
        // Filter by lesson if provided
        if ($request->has('lesson_id')) {
            $query->where('lesson_id', $request->lesson_id);
        }
        
        // Filter by part of speech if provided
        if ($request->has('part_of_speech')) {
            $query->where('part_of_speech', $request->part_of_speech);
        }
        
        // Filter by JLPT level if provided
        if ($request->has('jlpt_level')) {
            $query->where('jlpt_level', $request->jlpt_level);
        }
        
        // Filter kanji worksheet eligible items
        if ($request->has('kanji_worksheet') && $request->kanji_worksheet == '1') {
            $query->forKanjiWorksheet();
        }
        
        $vocabulary = $query->orderBy('lesson_id')->orderBy('frequency_rank')->get();
        $lessons = Lesson::orderBy('chapter')->get();
        
        return view('vocabulary.index', compact('vocabulary', 'lessons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lessons = Lesson::orderBy('chapter')->get();
        return view('vocabulary.create', compact('lessons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|unique:vocabulary',
            'lesson_id' => 'required|exists:lessons,id',
            'word_english' => 'required|string|max:255',
            'word_japanese' => 'nullable|string|max:255',
            'word_furigana' => 'nullable|string|max:500',
            'part_of_speech' => 'required|in:noun,verb,adjective,adverb,particle,conjunction,interjection,counter,expression',
            'jlpt_level' => 'nullable|in:N5,N4,N3,N2,N1',
            'frequency_rank' => 'nullable|integer|min:1',
            'include_in_kanji_worksheet' => 'boolean',
        ]);

        $vocabulary = Vocabulary::create($validated);
        return redirect()->route('vocabulary.show', $vocabulary)->with('success', 'Vocabulary created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $vocabulary = Vocabulary::with('lesson')->findOrFail($id);
        return view('vocabulary.show', compact('vocabulary'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $vocabulary = Vocabulary::findOrFail($id);
        $lessons = Lesson::orderBy('chapter')->get();
        return view('vocabulary.edit', compact('vocabulary', 'lessons'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $vocabulary = Vocabulary::findOrFail($id);
        
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'word_english' => 'required|string|max:255',
            'word_japanese' => 'nullable|string|max:255',
            'word_furigana' => 'nullable|string|max:500',
            'part_of_speech' => 'required|in:noun,verb,adjective,adverb,particle,conjunction,interjection,counter,expression',
            'jlpt_level' => 'nullable|in:N5,N4,N3,N2,N1',
            'frequency_rank' => 'nullable|integer|min:1',
            'include_in_kanji_worksheet' => 'boolean',
        ]);

        $vocabulary->update($validated);
        return redirect()->route('vocabulary.show', $vocabulary)->with('success', 'Vocabulary updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vocabulary = Vocabulary::findOrFail($id);
        $vocabulary->delete();
        return redirect()->route('vocabulary.index')->with('success', 'Vocabulary deleted successfully!');
    }
    
    /**
     * Display vocabulary items suitable for kanji worksheets
     */
    public function kanjiWorksheet(Request $request)
    {
        $query = Vocabulary::forKanjiWorksheet()->with('lesson');
        
        if ($request->has('lesson_id')) {
            $query->where('lesson_id', $request->lesson_id);
        }
        
        $vocabulary = $query->orderBy('lesson_id')->get();
        $lessons = Lesson::orderBy('chapter')->get();
        
        return view('vocabulary.kanji-worksheet', compact('vocabulary', 'lessons'));
    }
}
