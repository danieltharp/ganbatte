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
        if ($request->has('lesson_id') && $request->lesson_id != '') {
            $query->where('lesson_id', $request->lesson_id);
        }
        
        // Filter by part of speech if provided
        if ($request->has('part_of_speech') && $request->part_of_speech != '') {
            $query->where('part_of_speech', $request->part_of_speech);
        }
        
        // Filter by JLPT level if provided
        if ($request->has('jlpt_level') && $request->jlpt_level != '') {
            $query->where('jlpt_level', $request->jlpt_level);
        }
        
        // Filter kanji worksheet eligible items
        if ($request->has('kanji_worksheet') && $request->kanji_worksheet == '1') {
            $query->forKanjiWorksheet();
        }
        
        $vocabulary = $query->orderBy('lesson_id')->orderBy('id')->get();
        $lessons = Lesson::orderBy('chapter')->get();
        
        return view('vocabulary.index', compact('vocabulary', 'lessons'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $vocabulary = Vocabulary::with([
            'lesson.vocabulary' => function ($query) {
                $query->orderBy('id');
            }
        ])->findOrFail($id);
        return view('vocabulary.show', compact('vocabulary'));
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
