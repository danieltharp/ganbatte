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
        
        // Load user's exercise attempts if authenticated
        $userAttempts = collect();
        if (auth()->check()) {
            $userAttempts = auth()->user()->exerciseAttempts()
                ->where('exercise_id', $exercise->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('exercises.show', compact('exercise', 'questions', 'userAttempts'));
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
                'original_score' => $scoreResult['points_earned'], // Store original auto-graded score
                'total_points' => $scoreResult['points_available'],
                'time_spent_seconds' => $timeSpent * 60, // Convert minutes to seconds
                'answers' => $responses,
                'question_results' => $scoreResult['question_results'],
                'manual_corrections' => [], // No manual corrections initially
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
                'original_percentage' => $attempt->original_percentage,
                'is_passed' => $attempt->is_passed,
                'question_results' => $attempt->question_results,
                'completed_at' => $attempt->completed_at->toISOString(),
                'duration' => $attempt->duration,
                'has_manual_corrections' => $attempt->hasManualCorrections(),
                'results_url' => route('exercises.results', $attempt->id),
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

    /**
     * Show detailed results for an exercise attempt
     */
    public function showResults(ExerciseAttempt $attempt)
    {
        if (!Auth::check() || $attempt->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if (!$attempt->is_completed) {
            abort(400, 'Exercise not completed');
        }

        // Get questions with details
        $questions = Question::whereIn('id', array_keys($attempt->answers))->get()->keyBy('id');

        // Enhance question results with question details and manual correction info
        $questionResults = collect($attempt->question_results)->map(function ($result) use ($questions) {
            $question = $questions->get($result['question_id']);
            
            return [
                'question_id' => $result['question_id'],
                'question_text' => $question->question_text ?? '',
                'question_type' => $question->type ?? '',
                'user_answer' => $result['user_answer'],
                'correct_answer' => $question->correct_answer ?? '',
                'is_correct' => $result['is_correct'],
                'manually_accepted' => $result['manually_accepted'] ?? false,
                'manual_reason' => $result['manual_reason'] ?? null,
                'points_earned' => $result['points_earned'],
                'points_available' => $result['points_available'],
                'can_be_corrected' => !$result['is_correct'] && !($result['manually_accepted'] ?? false),
                'explanation' => $question->explanation_text ?? '',
            ];
        });

        return view('exercises.results', compact('attempt', 'questionResults'));
    }

    /**
     * Allow user to manually accept an incorrect answer as correct
     */
    public function acceptAnswer(Request $request, ExerciseAttempt $attempt): JsonResponse
    {
        if (!Auth::check() || $attempt->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$attempt->is_completed) {
            return response()->json(['error' => 'Exercise not completed'], 400);
        }

        $validated = $request->validate([
            'question_id' => 'required|string',
            'reason' => 'nullable|string|max:500', // Optional reason for manual acceptance
        ]);

        $questionId = $validated['question_id'];
        $reason = $validated['reason'] ?? null;

        // Check if question exists in this exercise
        $exercise = $attempt->exercise;
        if (!in_array($questionId, $exercise->question_ids ?? [])) {
            return response()->json(['error' => 'Question not found in this exercise'], 404);
        }

        // Get the question to determine points
        $question = Question::find($questionId);
        if (!$question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        try {
            DB::beginTransaction();

            // Add to manual corrections if not already there
            $manualCorrections = $attempt->manual_corrections ?? [];
            if (!in_array($questionId, $manualCorrections)) {
                $manualCorrections[] = $questionId;
                
                // Update question results to mark as manually accepted
                $questionResults = $attempt->question_results;
                foreach ($questionResults as &$result) {
                    if ($result['question_id'] === $questionId) {
                        $result['manually_accepted'] = true;
                        $result['manual_reason'] = $reason;
                        $result['points_earned'] = $result['points_available']; // Give full credit
                        break;
                    }
                }

                // Recalculate total score
                $newScore = $attempt->original_score;
                foreach ($manualCorrections as $correctedQuestionId) {
                    $correctedQuestion = Question::find($correctedQuestionId);
                    if ($correctedQuestion) {
                        $newScore += $correctedQuestion->points;
                    }
                }

                // Update attempt record
                $attempt->update([
                    'score' => $newScore,
                    'manual_corrections' => $manualCorrections,
                    'question_results' => $questionResults,
                    'last_corrected_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'question_id' => $questionId,
                'points_earned' => $question->points,
                'new_total_score' => $attempt->score,
                'new_percentage' => $attempt->percentage,
                'original_percentage' => $attempt->original_percentage,
                'improvement' => $attempt->manual_improvement,
                'is_passed' => $attempt->is_passed,
                'message' => "Answer accepted! Score improved to {$attempt->percentage}%",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Failed to accept answer',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get detailed results for an exercise attempt including manual corrections
     */
    public function getResults(Request $request, ExerciseAttempt $attempt): JsonResponse
    {
        if (!Auth::check() || $attempt->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$attempt->is_completed) {
            return response()->json(['error' => 'Exercise not completed'], 400);
        }

        // Get questions with details
        $questions = Question::whereIn('id', array_keys($attempt->answers))->get()->keyBy('id');

        // Enhance question results with question details and manual correction info
        $enhancedResults = collect($attempt->question_results)->map(function ($result) use ($questions, $attempt) {
            $question = $questions->get($result['question_id']);
            
            return [
                'question_id' => $result['question_id'],
                'question_text' => $question->question_text ?? '',
                'question_type' => $question->type ?? '',
                'user_answer' => $result['user_answer'],
                'correct_answer' => $question->correct_answer ?? '',
                'is_correct' => $result['is_correct'],
                'manually_accepted' => $result['manually_accepted'] ?? false,
                'manual_reason' => $result['manual_reason'] ?? null,
                'points_earned' => $result['points_earned'],
                'points_available' => $result['points_available'],
                'can_be_corrected' => !$result['is_correct'] && !($result['manually_accepted'] ?? false),
                'explanation' => $question->explanation_text ?? '',
            ];
        });

        return response()->json([
            'attempt_id' => $attempt->id,
            'exercise_id' => $attempt->exercise_id,
            'exercise_name' => $attempt->exercise->name,
            'points_earned' => $attempt->score,
            'original_score' => $attempt->original_score,
            'points_available' => $attempt->total_points,
            'percentage' => $attempt->percentage,
            'original_percentage' => $attempt->original_percentage,
            'is_passed' => $attempt->is_passed,
            'duration' => $attempt->duration,
            'completed_at' => $attempt->completed_at->toISOString(),
            'has_manual_corrections' => $attempt->hasManualCorrections(),
            'manual_correction_count' => $attempt->manual_correction_count,
            'last_corrected_at' => $attempt->last_corrected_at?->toISOString(),
            'question_results' => $enhancedResults,
        ]);
    }
}
