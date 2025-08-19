<?php

namespace App\Services;

use App\Models\User;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\QuestionResponse;
use Illuminate\Support\Collection;

class GradingService
{
    /**
     * Semester ranges based on lesson numbers
     */
    const SEMESTER_RANGES = [
        1 => ['start' => 0, 'end' => 12],   // Semester 1: Lessons 0-12
        2 => ['start' => 13, 'end' => 25],  // Semester 2: Lessons 13-25
        3 => ['start' => 26, 'end' => 38],  // Semester 3: Lessons 26-38
        4 => ['start' => 39, 'end' => 50],  // Semester 4: Lessons 39-50
    ];

    /**
     * Calculate flat points for a single question response
     */
    public function calculateQuestionPoints(Question $question, $userAnswer): array
    {
        $isCorrect = $this->validateAnswer($question, $userAnswer);
        
        return [
            'is_correct' => $isCorrect,
            'points_earned' => $isCorrect ? $question->points : 0,
            'points_available' => $question->points,
        ];
    }

    /**
     * Validate user answer against correct answer
     */
    public function validateAnswer(Question $question, $userAnswer): bool
    {
        $correctAnswer = $question->correct_answer;
        
        // Handle different question types
        switch ($question->type) {
            case 'multiple_choice':
                return $this->validateMultipleChoice($correctAnswer, $userAnswer);
            
            case 'fill_blank':
            case 'translation_j_to_e':
            case 'translation_e_to_j':
                return $this->validateTextAnswer($correctAnswer, $userAnswer);
            
            case 'sentence_ordering':
                return $this->validateSequence($correctAnswer, $userAnswer);
            
            default:
                // Default to exact match for other types
                return $correctAnswer === $userAnswer;
        }
    }

    /**
     * Calculate lesson grade based on all user's responses for that lesson
     */
    public function calculateLessonGrade(User $user, Lesson $lesson): array
    {
        // Get all completed test attempts for this lesson
        $testAttempts = $user->testAttempts()
            ->whereHas('test', function ($query) use ($lesson) {
                $query->whereJsonContains('lesson_ids', $lesson->id);
            })
            ->completed()
            ->with('responses.question')
            ->get();

        $totalPointsAvailable = 0;
        $totalPointsEarned = 0;

        foreach ($testAttempts as $attempt) {
            foreach ($attempt->responses as $response) {
                if ($response->question->lesson_id === $lesson->id) {
                    $totalPointsAvailable += $response->question->points;
                    $totalPointsEarned += $response->points_earned;
                }
            }
        }

        $percentage = $totalPointsAvailable > 0 
            ? round(($totalPointsEarned / $totalPointsAvailable) * 100, 2)
            : 0;

        return [
            'lesson_id' => $lesson->id,
            'lesson_name' => $lesson->title_english,
            'points_earned' => $totalPointsEarned,
            'points_available' => $totalPointsAvailable,
            'percentage' => $percentage,
        ];
    }

    /**
     * Calculate semester grade based on lesson averages
     */
    public function calculateSemesterGrade(User $user, int $semesterNumber): array
    {
        if (!isset(self::SEMESTER_RANGES[$semesterNumber])) {
            throw new \InvalidArgumentException("Invalid semester number: $semesterNumber");
        }

        $range = self::SEMESTER_RANGES[$semesterNumber];
        
        // Get lessons in the semester range
        $lessons = Lesson::whereBetween('chapter', [$range['start'], $range['end']])
            ->orderBy('chapter')
            ->get();

        $lessonGrades = [];
        $totalPercentage = 0;
        $completedLessons = 0;

        foreach ($lessons as $lesson) {
            $grade = $this->calculateLessonGrade($user, $lesson);
            
            if ($grade['points_available'] > 0) {
                $lessonGrades[] = $grade;
                $totalPercentage += $grade['percentage'];
                $completedLessons++;
            }
        }

        $semesterPercentage = $completedLessons > 0 
            ? round($totalPercentage / $completedLessons, 2)
            : 0;

        return [
            'semester' => $semesterNumber,
            'semester_range' => "Lessons {$range['start']}-{$range['end']}",
            'percentage' => $semesterPercentage,
            'completed_lessons' => $completedLessons,
            'total_lessons' => $lessons->count(),
            'lesson_grades' => $lessonGrades,
        ];
    }

    /**
     * Get all semester grades for a user
     */
    public function getAllSemesterGrades(User $user): array
    {
        $semesters = [];
        
        for ($i = 1; $i <= 4; $i++) {
            $semesters[] = $this->calculateSemesterGrade($user, $i);
        }
        
        return $semesters;
    }

    /**
     * Calculate exercise score and create attempt record
     */
    public function scoreExercise(User $user, $exercise, array $responses): array
    {
        $questions = Question::whereIn('id', array_keys($responses))->get();
        
        $totalPoints = $questions->sum('points');
        $earnedPoints = 0;
        $questionResults = [];

        foreach ($questions as $question) {
            $userAnswer = $responses[$question->id] ?? null;
            $result = $this->calculateQuestionPoints($question, $userAnswer);
            
            $questionResults[] = [
                'question_id' => $question->id,
                'user_answer' => $userAnswer,
                'is_correct' => $result['is_correct'],
                'points_earned' => $result['points_earned'],
                'points_available' => $result['points_available'],
            ];
            
            $earnedPoints += $result['points_earned'];
        }

        $percentage = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;

        return [
            'exercise_id' => $exercise->id,
            'user_id' => $user->id,
            'points_earned' => $earnedPoints,
            'points_available' => $totalPoints,
            'percentage' => $percentage,
            'question_results' => $questionResults,
            'completed_at' => now(),
        ];
    }

    /**
     * Validate multiple choice answer
     */
    protected function validateMultipleChoice($correct, $userAnswer): bool
    {
        // Handle both single and multiple selections
        if (is_array($correct)) {
            return is_array($userAnswer) && array_diff($correct, $userAnswer) === [] && array_diff($userAnswer, $correct) === [];
        }
        
        return $correct == $userAnswer;
    }

    /**
     * Validate text-based answers with some flexibility
     */
    protected function validateTextAnswer($correct, $userAnswer): bool
    {
        if (is_array($correct)) {
            // Multiple acceptable answers
            $userAnswerNormalized = $this->normalizeText($userAnswer);
            
            foreach ($correct as $acceptableAnswer) {
                if ($this->normalizeText($acceptableAnswer) === $userAnswerNormalized) {
                    return true;
                }
            }
            return false;
        }
        
        return $this->normalizeText($correct) === $this->normalizeText($userAnswer);
    }

    /**
     * Validate sequence/ordering answers
     */
    protected function validateSequence($correct, $userAnswer): bool
    {
        if (!is_array($correct) || !is_array($userAnswer)) {
            return false;
        }
        
        return $correct === $userAnswer;
    }

    /**
     * Normalize text for comparison (trim, lowercase, remove extra spaces)
     */
    protected function normalizeText($text): string
    {
        if ($text === null) return '';
        
        // Handle arrays by converting to string
        if (is_array($text)) {
            $text = implode(' ', $text);
        }
        
        // Convert to string if not already
        $text = (string) $text;
        
        return trim(strtolower(preg_replace('/\s+/', ' ', $text)));
    }

    /**
     * Get semester number for a given lesson
     */
    public function getSemesterForLesson(int $lessonChapter): ?int
    {
        foreach (self::SEMESTER_RANGES as $semester => $range) {
            if ($lessonChapter >= $range['start'] && $lessonChapter <= $range['end']) {
                return $semester;
            }
        }
        
        return null;
    }
}
