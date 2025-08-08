<?php

namespace App\Http\Controllers;

use App\Models\Vocabulary;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class VocabularyQuizController extends Controller
{
    /**
     * Display the quiz setup form
     */
    public function index()
    {
        $lessons = Lesson::orderBy('chapter')->get();
        return view('vocabulary.quiz.index', compact('lessons'));
    }

    /**
     * Start a new quiz session
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'lesson_from' => 'required|exists:lessons,id',
            'lesson_to' => 'required|exists:lessons,id',
            'difficulty' => 'required|in:easy,hard',
            'mode' => 'required|in:recognition,recall,mixed',
            'question_count' => 'nullable|integer|min:5|max:50',
        ]);

        // Get lesson range
        $lessonFrom = Lesson::find($validated['lesson_from']);
        $lessonTo = Lesson::find($validated['lesson_to']);
        
        // Get vocabulary items from the lesson range
        $vocabulary = Vocabulary::with('lesson')
            ->whereHas('lesson', function ($query) use ($lessonFrom, $lessonTo) {
                $query->whereBetween('chapter', [$lessonFrom->chapter, $lessonTo->chapter]);
            })
            ->get();

        if ($vocabulary->isEmpty()) {
            return redirect()->route('vocabulary.quiz.index')
                ->with('error', 'No vocabulary found for the selected lesson range.');
        }

        // Prepare quiz questions
        $questionCount = $validated['question_count'] ?? min(20, $vocabulary->count());
        $questions = $this->generateQuizQuestions(
            $vocabulary,
            $validated['difficulty'],
            $validated['mode'],
            $questionCount
        );

        // Store quiz configuration in session (no database persistence)
        session([
            'quiz_config' => [
                'difficulty' => $validated['difficulty'],
                'mode' => $validated['mode'],
                'lesson_from' => $lessonFrom->title_english,
                'lesson_to' => $lessonTo->title_english,
                'total_questions' => count($questions),
            ],
            'quiz_questions' => $questions,
            'quiz_started_at' => now(),
        ]);

        return redirect()->route('vocabulary.quiz.take');
    }

    /**
     * Display the quiz interface
     */
    public function take()
    {
        if (!session()->has('quiz_questions')) {
            return redirect()->route('vocabulary.quiz.index')
                ->with('error', 'No active quiz session. Please start a new quiz.');
        }

        $config = session('quiz_config');
        $questions = session('quiz_questions');

        return view('vocabulary.quiz.take', compact('config', 'questions'));
    }

    /**
     * Process quiz submission and show results
     */
    public function submit(Request $request)
    {
        if (!session()->has('quiz_questions')) {
            return response()->json(['error' => 'No active quiz session'], 400);
        }

        $answers = $request->input('answers', []);
        $questions = session('quiz_questions');
        $results = [];
        $score = 0;

        foreach ($questions as $index => $question) {
            $userAnswer = $answers[$index] ?? '';
            $isCorrect = false;

            if ($question['difficulty'] === 'easy') {
                // Multiple choice - check if selected option matches correct answer
                $isCorrect = $userAnswer === $question['correct_answer'];
            } else {
                // Hard mode - normalize and compare typed answer
                $normalizedUserAnswer = $this->normalizeAnswer($userAnswer);
                $normalizedCorrectAnswer = $this->normalizeAnswer($question['correct_answer']);
                
                // Check primary answer
                $isCorrect = $normalizedUserAnswer === $normalizedCorrectAnswer;
                
                // Check alternative answers from furigana extraction
                if (!$isCorrect && isset($question['alternative_answers'])) {
                    foreach ($question['alternative_answers'] as $alt) {
                        if ($this->normalizeAnswer($alt) === $normalizedUserAnswer) {
                            $isCorrect = true;
                            break;
                        }
                    }
                }
                
                // Check also_accepted answers from database
                if (!$isCorrect && isset($question['also_accepted'])) {
                    foreach ($question['also_accepted'] as $alt) {
                        if ($this->normalizeAnswer($alt) === $normalizedUserAnswer) {
                            $isCorrect = true;
                            break;
                        }
                    }
                }
            }

            if ($isCorrect) {
                $score++;
            }

            $results[] = [
                'question' => $question['question'],
                'correct_answer' => $question['correct_answer'],
                'display_answer' => $question['display_answer'] ?? $question['correct_answer'],
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect,
                'options' => $question['options'] ?? null,
            ];
        }

        // Calculate time taken (ensure positive value)
        $startTime = session('quiz_started_at');
        $timeTaken = $startTime ? abs(now()->diffInSeconds($startTime)) : 0;
        
        // Clear quiz session
        session()->forget(['quiz_questions', 'quiz_config', 'quiz_started_at']);

        return response()->json([
            'score' => $score,
            'total' => count($questions),
            'percentage' => round(($score / count($questions)) * 100),
            'time_taken' => $timeTaken,
            'results' => $results,
        ]);
    }

    /**
     * Generate quiz questions based on configuration
     */
    private function generateQuizQuestions(Collection $vocabulary, string $difficulty, string $mode, int $count): array
    {
        $questions = [];
        $selectedVocab = $vocabulary->random(min($count, $vocabulary->count()));

        foreach ($selectedVocab as $vocab) {
            $questionType = $mode;
            
            if ($mode === 'mixed') {
                $questionType = rand(0, 1) ? 'recognition' : 'recall';
            }

            $question = [
                'id' => $vocab->id,
                'difficulty' => $difficulty,
                'type' => $questionType,
            ];

            if ($questionType === 'recognition') {
                // Japanese to English
                $question['question'] = $vocab->word_japanese;
                $question['question_furigana'] = $vocab->word_furigana;
                $question['correct_answer'] = $vocab->word_english;
                $question['display_answer'] = $vocab->word_english;
                
                // Add accepted English alternatives if they exist
                if ($vocab->also_accepted && isset($vocab->also_accepted['english'])) {
                    $question['also_accepted'] = $vocab->also_accepted['english'];
                }
            } else {
                // English to Japanese (recall)
                $question['question'] = $vocab->word_english;
                
                // For easy mode (multiple choice), use furigana form as correct answer
                // For hard mode (typed), use plain Japanese for comparison
                if ($difficulty === 'easy') {
                    $question['correct_answer'] = $vocab->word_furigana ?: $vocab->word_japanese;
                } else {
                    $question['correct_answer'] = $vocab->word_japanese;
                }
                
                $question['display_answer'] = $vocab->word_furigana ?: $vocab->word_japanese;
                
                // For typed answers, accept both kanji and hiragana forms
                $alternatives = [];
                if ($vocab->word_furigana) {
                    // Extract just hiragana from furigana format
                    $hiragana = $this->extractHiragana($vocab->word_furigana);
                    if ($hiragana && $hiragana !== $vocab->word_japanese) {
                        $alternatives[] = $hiragana;
                    }
                }
                $question['alternative_answers'] = $alternatives;
                
                // Add accepted Japanese alternatives if they exist
                if ($vocab->also_accepted && isset($vocab->also_accepted['japanese'])) {
                    $question['also_accepted'] = $vocab->also_accepted['japanese'];
                }
            }

            // Generate options for easy mode (multiple choice)
            if ($difficulty === 'easy') {
                $question['options'] = $this->generateOptions(
                    $vocab,
                    $vocabulary,
                    $questionType
                );
            }

            $questions[] = $question;
        }

        // Shuffle questions
        shuffle($questions);

        return $questions;
    }

    /**
     * Generate multiple choice options
     */
    private function generateOptions(Vocabulary $correctVocab, Collection $allVocabulary, string $questionType): array
    {
        $options = [];
        
        // For recall mode, use furigana if available for display
        $correctAnswer = $questionType === 'recognition' 
            ? $correctVocab->word_english 
            : ($correctVocab->word_furigana ?: $correctVocab->word_japanese);

        // Get distractors from the same lesson or nearby lessons
        $distractors = $allVocabulary
            ->where('id', '!=', $correctVocab->id)
            ->filter(function ($vocab) use ($correctVocab, $questionType) {
                // Filter out very similar items
                if ($questionType === 'recognition') {
                    return $vocab->word_english !== $correctVocab->word_english;
                } else {
                    return $vocab->word_japanese !== $correctVocab->word_japanese;
                }
            })
            ->random(min(3, $allVocabulary->count() - 1));

        foreach ($distractors as $distractor) {
            $options[] = $questionType === 'recognition'
                ? $distractor->word_english
                : ($distractor->word_furigana ?: $distractor->word_japanese);
        }

        $options[] = $correctAnswer;
        shuffle($options);

        return $options;
    }

    /**
     * Normalize answer for comparison (remove spaces, convert to lowercase, etc.)
     */
    private function normalizeAnswer(string $answer): string
    {
        // Remove spaces, convert to lowercase for English
        $normalized = trim($answer);
        
        // For English text, make it case-insensitive
        if (preg_match('/^[a-zA-Z\s\-\'\.]+$/', $normalized)) {
            $normalized = strtolower($normalized);
        }
        
        // Remove common punctuation that might vary
        $normalized = str_replace(['、', '。', '.', ','], '', $normalized);
        
        return $normalized;
    }

    /**
     * Extract hiragana from furigana format
     */
    private function extractHiragana(string $furigana): ?string
    {
        // Extract just the reading from format like {漢字|かん|じ}
        if (preg_match_all('/\{[^|]+\|([^}]+)\}/', $furigana, $matches)) {
            $readings = [];
            foreach ($matches[1] as $reading) {
                $parts = explode('|', $reading);
                $readings = array_merge($readings, $parts);
            }
            return implode('', $readings);
        }
        
        // If no furigana format found, check if it's already just hiragana
        if (preg_match('/^[\p{Hiragana}ー]+$/u', $furigana)) {
            return $furigana;
        }
        
        return null;
    }
}
