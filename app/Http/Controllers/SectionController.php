<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\UserSectionProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sections = Section::with(['lesson'])->orderBy('lesson_id')->orderBy('order_weight')->get();
        
        // If user is authenticated, load their progress
        if (Auth::check()) {
            $userProgress = Auth::user()->sectionProgress()->pluck('completed_at', 'section_id');
            $sections->each(function ($section) use ($userProgress) {
                $section->user_completed_at = $userProgress->get($section->id);
                $section->is_user_completed = !is_null($section->user_completed_at);
            });
        }
        
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
        
        // Load user progress if authenticated
        $userProgress = null;
        if (Auth::check()) {
            $userProgress = Auth::user()->getProgressForSection($section->id);
        }
        
        return view('sections.show', compact('section', 'userProgress'));
    }

    /**
     * Mark a section as completed for the authenticated user
     */
    public function markComplete(Request $request, Section $section): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        if (!$section->isTrackable()) {
            return response()->json(['error' => 'This section does not allow completion tracking'], 400);
        }

        $timeSpent = $request->input('time_spent_minutes', 0);
        $notes = $request->input('notes', null);

        // Find existing progress or create new one
        $progress = UserSectionProgress::where([
            'user_id' => Auth::id(),
            'section_id' => $section->id,
        ])->first();

        if ($progress) {
            // Update existing record - increment attempts
            $progress->update([
                'completed_at' => now(),
                'attempts' => $progress->attempts + 1,
                'time_spent_minutes' => $timeSpent,
                'notes' => $notes,
            ]);
        } else {
            // Create new record - start with 1 attempt
            $progress = UserSectionProgress::create([
                'user_id' => Auth::id(),
                'section_id' => $section->id,
                'completed_at' => now(),
                'attempts' => 1,
                'time_spent_minutes' => $timeSpent,
                'notes' => $notes,
            ]);
        }

        return response()->json([
            'success' => true,
            'completed_at' => $progress->completed_at->toISOString(),
            'attempts' => $progress->attempts,
            'message' => 'Section marked as completed',
        ]);
    }

    /**
     * Reset section completion for the authenticated user
     */
    public function resetCompletion(Request $request, Section $section): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $progress = Auth::user()->getProgressForSection($section->id);
        
        if (!$progress) {
            return response()->json(['error' => 'No progress found for this section'], 404);
        }

        $progress->resetCompletion();

        return response()->json([
            'success' => true,
            'message' => 'Section completion reset',
        ]);
    }
}
