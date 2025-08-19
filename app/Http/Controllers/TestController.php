<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\Question;
use App\Models\QuestionResponse;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    protected GradingService $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Display a listing of published tests
     */
    public function index()
    {
        $tests = Test::published()->with(['lessons'])->get();
        return view('tests.index', compact('tests'));
    }

    /**
     * Display the specified test
     */
    public function show(Test $test)
    {
        $test->load(['questions' => function ($query) {
            $query->orderBy('pivot_order');
        }]);

        // Get user's previous attempts if authenticated
        $userAttempts = [];
        if (Auth::check()) {
            $userAttempts = Auth::user()->testAttempts()
                ->where('test_id', $test->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('tests.show', compact('test', 'userAttempts'));
    }

    /**
     * Start a new test attempt
     */
    public function start(Request $request, Test $test): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        // Check if retakes are allowed
        if (!$test->allow_retakes) {
            $existingAttempt = Auth::user()->testAttempts()
                ->where('test_id', $test->id)
                ->completed()
                ->first();
                
            if ($existingAttempt) {
                return response()->json(['error' => 'Retakes not allowed for this test'], 403);
            }
        }

        try {
            DB::beginTransaction();

            $attempt = TestAttempt::create([
                'user_id' => Auth::id(),
                'test_id' => $test->id,
                'started_at' => now(),
                'total_points' => $test->questions->sum('points'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'attempt_id' => $attempt->id,
                'test_id' => $test->id,
                'started_at' => $attempt->started_at->toISOString(),
                'time_limit_seconds' => $test->time_limit_minutes ? $test->time_limit_minutes * 60 : null,
                'total_questions' => $test->questions->count(),
                'total_points' => $attempt->total_points,
                'message' => 'Test attempt started successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Failed to start test attempt',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Submit test attempt with all responses
     */
    public function submit(Request $request, TestAttempt $attempt): JsonResponse
    {
        if (!Auth::check() || $attempt->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($attempt->is_completed) {
            return response()->json(['error' => 'Test attempt already completed'], 400);
        }

        $responses = $request->input('responses', []);
        $timeSpent = $request->input('time_spent_seconds', 0);

        if (empty($responses)) {
            return response()->json(['error' => 'No responses provided'], 400);
        }

        try {
            DB::beginTransaction();

            $totalEarned = 0;
            $questionResults = [];

            // Process each response
            foreach ($responses as $questionId => $userAnswer) {
                $question = Question::find($questionId);
                if (!$question) continue;

                $result = $this->gradingService->calculateQuestionPoints($question, $userAnswer);

                // Create question response record
                QuestionResponse::create([
                    'test_attempt_id' => $attempt->id,
                    'question_id' => $questionId,
                    'user_answer' => $userAnswer,
                    'is_correct' => $result['is_correct'],
                    'points_earned' => $result['points_earned'],
                    'time_spent_seconds' => $timeSpent / count($responses), // Approximate time per question
                    'answered_at' => now(),
                ]);

                $totalEarned += $result['points_earned'];
                $questionResults[] = $result;
            }

            // Update test attempt
            $percentage = $attempt->total_points > 0 
                ? round(($totalEarned / $attempt->total_points) * 100, 2)
                : 0;

            $attempt->update([
                'completed_at' => now(),
                'score' => $totalEarned,
                'time_spent_seconds' => $timeSpent,
                'answers' => $responses,
                'is_completed' => true,
                'is_passed' => $percentage >= $attempt->test->passing_score,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'attempt_id' => $attempt->id,
                'points_earned' => $totalEarned,
                'points_available' => $attempt->total_points,
                'percentage' => $percentage,
                'is_passed' => $attempt->is_passed,
                'passing_score' => $attempt->test->passing_score,
                'completed_at' => $attempt->completed_at->toISOString(),
                'duration' => $attempt->duration,
                'message' => $attempt->is_passed 
                    ? "Congratulations! You passed with {$percentage}%" 
                    : "Test completed. Score: {$percentage}% (needed {$attempt->test->passing_score}%)",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Failed to submit test',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get test results for a specific attempt
     */
    public function results(TestAttempt $attempt): JsonResponse
    {
        if (!Auth::check() || $attempt->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$attempt->is_completed) {
            return response()->json(['error' => 'Test not yet completed'], 400);
        }

        $attempt->load(['responses.question', 'test']);

        return response()->json([
            'attempt_id' => $attempt->id,
            'test_name' => $attempt->test->name,
            'points_earned' => $attempt->score,
            'points_available' => $attempt->total_points,
            'percentage' => $attempt->percentage,
            'is_passed' => $attempt->is_passed,
            'duration' => $attempt->duration,
            'completed_at' => $attempt->completed_at->toISOString(),
            'question_results' => $attempt->responses->map(function ($response) {
                return [
                    'question_id' => $response->question_id,
                    'question_text' => $response->question->question_text,
                    'user_answer' => $response->user_answer,
                    'correct_answer' => $response->question->correct_answer,
                    'is_correct' => $response->is_correct,
                    'points_earned' => $response->points_earned,
                    'points_available' => $response->question->points,
                    'explanation' => $response->question->explanation_text,
                ];
            }),
        ]);
    }
}
