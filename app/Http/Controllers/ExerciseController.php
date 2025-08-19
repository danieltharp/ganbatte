<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Question;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExerciseController extends Controller
{
    protected GradingService $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

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
            $questions = Question::whereIn('id', $exercise->question_ids)
                ->get()
                ->sortBy(function ($question) use ($exercise) {
                    return array_search($question->id, $exercise->question_ids);
                })
                ->values();
        }
        
        return view('exercises.show', compact('exercise', 'questions'));
    }

    /**
     * Submit exercise responses and calculate score
     */
    public function submit(Request $request, Exercise $exercise): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $responses = $request->input('responses', []);
        $timeSpent = $request->input('time_spent_minutes', 0);

        if (empty($responses)) {
            return response()->json(['error' => 'No responses provided'], 400);
        }

        try {
            DB::beginTransaction();

            // Calculate score using the grading service
            $scoreResult = $this->gradingService->scoreExercise(Auth::user(), $exercise, $responses);

            // TODO: Store exercise attempt record when that table is created
            // For now, we'll just return the calculated scores

            DB::commit();

            return response()->json([
                'success' => true,
                'exercise_id' => $exercise->id,
                'exercise_name' => $exercise->name,
                'points_earned' => $scoreResult['points_earned'],
                'points_available' => $scoreResult['points_available'],
                'percentage' => $scoreResult['percentage'],
                'question_results' => $scoreResult['question_results'],
                'completed_at' => $scoreResult['completed_at']->toISOString(),
                'message' => "Exercise completed! Score: {$scoreResult['percentage']}%",
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Failed to process exercise submission',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get exercise statistics for a user
     */
    public function getStats(Request $request, Exercise $exercise): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        // TODO: Implement when exercise attempts table is created
        // For now, return basic exercise info
        
        $questions = [];
        if ($exercise->question_ids && count($exercise->question_ids) > 0) {
            $questions = Question::whereIn('id', $exercise->question_ids)->get();
        }

        $totalPoints = $questions->sum('points');

        return response()->json([
            'exercise_id' => $exercise->id,
            'exercise_name' => $exercise->name,
            'total_questions' => count($questions),
            'total_points' => $totalPoints,
            'lesson_id' => $exercise->lesson_id,
            'semester' => $this->gradingService->getSemesterForLesson($exercise->lesson->chapter ?? 0),
        ]);
    }
}
