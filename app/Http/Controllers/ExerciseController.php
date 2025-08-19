<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\ExerciseAttempt;
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

            // Store exercise attempt record
            $attempt = ExerciseAttempt::create([
                'user_id' => Auth::id(),
                'exercise_id' => $exercise->id,
                'started_at' => now()->subMinutes($timeSpent), // Approximate start time
                'completed_at' => now(),
                'score' => $scoreResult['points_earned'],
                'total_points' => $scoreResult['points_available'],
                'time_spent_seconds' => $timeSpent * 60, // Convert minutes to seconds
                'answers' => $responses,
                'question_results' => $scoreResult['question_results'],
                'is_completed' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'attempt_id' => $attempt->id,
                'exercise_id' => $exercise->id,
                'exercise_name' => $exercise->name,
                'points_earned' => $attempt->score,
                'points_available' => $attempt->total_points,
                'percentage' => $attempt->percentage,
                'is_passed' => $attempt->is_passed,
                'question_results' => $attempt->question_results,
                'completed_at' => $attempt->completed_at->toISOString(),
                'duration' => $attempt->duration,
                'message' => $attempt->is_passed 
                    ? "Excellent! You scored {$attempt->percentage}%" 
                    : "Exercise completed. Score: {$attempt->percentage}%",
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

        $questions = [];
        if ($exercise->question_ids && count($exercise->question_ids) > 0) {
            $questions = Question::whereIn('id', $exercise->question_ids)->get();
        }

        $totalPoints = $questions->sum('points');

        // Get user's attempts for this exercise
        $userAttempts = Auth::user()->exerciseAttempts()
            ->where('exercise_id', $exercise->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $latestAttempt = $userAttempts->first();
        $bestAttempt = $userAttempts->where('is_completed', true)
            ->sortByDesc('score')
            ->sortBy('time_spent_seconds')
            ->first();

        return response()->json([
            'exercise_id' => $exercise->id,
            'exercise_name' => $exercise->name,
            'total_questions' => count($questions),
            'total_points' => $totalPoints,
            'lesson_id' => $exercise->lesson_id,
            'semester' => $this->gradingService->getSemesterForLesson($exercise->lesson->chapter ?? 0),
            'user_stats' => [
                'total_attempts' => $userAttempts->count(),
                'completed_attempts' => $userAttempts->where('is_completed', true)->count(),
                'best_score' => $bestAttempt ? $bestAttempt->percentage : null,
                'latest_score' => $latestAttempt && $latestAttempt->is_completed ? $latestAttempt->percentage : null,
                'has_completed' => $userAttempts->where('is_completed', true)->isNotEmpty(),
            ],
        ]);
    }
}
