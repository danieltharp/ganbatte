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
        $lesson = Lesson::with([
            'vocabulary', 
            'grammarPoints', 
            'questions', 
            'worksheets', 
            'pages',
            'articles'
        ])->findOrFail($id);
        
        // Load user progress if authenticated
        $sectionProgress = collect();
        $exerciseProgress = collect();
        
        if (auth()->check()) {
            $user = auth()->user();
            
            // Get all section and exercise IDs from lesson pages
            $sectionIds = collect();
            $exerciseIds = collect();
            
            foreach ($lesson->pages as $page) {
                // Use the Page model's content attribute which processes content_list
                $pageContent = $page->content;
                
                foreach ($pageContent as $contentItem) {
                    if ($contentItem->type === 'section') {
                        $sectionIds->push($contentItem->id);
                    } elseif ($contentItem->type === 'exercise') {
                        $exerciseIds->push($contentItem->id);
                    }
                }
            }
            
            $sectionIds = $sectionIds->unique();
            $exerciseIds = $exerciseIds->unique();
            
            // Load user's section progress
            if ($sectionIds->isNotEmpty()) {
                $sectionProgress = $user->sectionProgress()
                    ->whereIn('section_id', $sectionIds)
                    ->get()
                    ->keyBy('section_id');
            }
            
            // Load user's exercise progress
            if ($exerciseIds->isNotEmpty()) {
                $exerciseProgress = $user->exerciseAttempts()
                    ->whereIn('exercise_id', $exerciseIds)
                    ->where('is_completed', true)
                    ->get()
                    ->groupBy('exercise_id')
                    ->map(function ($attempts) {
                        // Get the best attempt for each exercise
                        return $attempts->sortByDesc('score')->sortBy('time_spent_seconds')->first();
                    });
            }
        }
        
        return view('lessons.show', compact('lesson', 'sectionProgress', 'exerciseProgress'));
    }
}
