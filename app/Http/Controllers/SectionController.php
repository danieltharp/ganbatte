<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sections = Section::with(['lesson'])->orderBy('lesson_id')->orderBy('order_weight')->get();
        return view('sections.index', compact('sections'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $section = Section::with([
            'lesson.vocabulary',
            'lesson.grammarPoints',
            'lesson.pages' => function ($query) {
                $query->orderBy('page_number');
            }
        ])->findOrFail($id);
        return view('sections.show', compact('section'));
    }
}
