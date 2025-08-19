<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    protected GradingService $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Get lesson grade for the authenticated user
     */
    public function getLessonGrade(Request $request, Lesson $lesson): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $grade = $this->gradingService->calculateLessonGrade(Auth::user(), $lesson);

        return response()->json($grade);
    }

    /**
     * Get semester grade for the authenticated user
     */
    public function getSemesterGrade(Request $request, int $semesterNumber): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        try {
            $semesterGrade = $this->gradingService->calculateSemesterGrade(Auth::user(), $semesterNumber);
            return response()->json($semesterGrade);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get all semester grades for the authenticated user
     */
    public function getAllGrades(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $allGrades = $this->gradingService->getAllSemesterGrades(Auth::user());

        // Calculate overall progress
        $totalLessons = collect($allGrades)->sum('total_lessons');
        $completedLessons = collect($allGrades)->sum('completed_lessons');
        $overallPercentage = collect($allGrades)
            ->where('completed_lessons', '>', 0)
            ->avg('percentage');

        return response()->json([
            'user_id' => Auth::id(),
            'overall_progress' => [
                'completed_lessons' => $completedLessons,
                'total_lessons' => $totalLessons,
                'completion_percentage' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0,
                'grade_average' => $overallPercentage ? round($overallPercentage, 2) : 0,
            ],
            'semesters' => $allGrades,
        ]);
    }

    /**
     * Get progress dashboard data
     */
    public function dashboard(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Get recent completed sections
        $recentSections = $user->completedSections()
            ->with('section.lesson')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        // Get recent test attempts
        $recentAttempts = $user->completedAttempts()
            ->with('test')
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get();

        // Get all grades for summary
        $allGrades = $this->gradingService->getAllSemesterGrades($user);

        return view('progress.dashboard', compact('recentSections', 'recentAttempts', 'allGrades'));
    }
}
