<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Lesson;
use App\Models\Vocabulary;
use App\Models\GrammarPoint;
use App\Models\Question;
use App\Models\Test;
use App\Models\Worksheet;

class LessonContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Importing lesson content from JSON files...');
        $this->command->info('Using upsert: existing content will be updated, new content will be created.');

        // Get all lesson files first to determine what lessons exist
        $lessonFiles = File::glob(resource_path('data/lessons/lesson-*.json'));
        
        foreach ($lessonFiles as $lessonFile) {
            $lessonNumber = $this->extractLessonNumber($lessonFile);
            $this->importLessonContent($lessonNumber);
        }

        $this->command->info('Lesson content import completed!');
    }

    private function extractLessonNumber($filename)
    {
        preg_match('/lesson-(\d+)\.json$/', basename($filename), $matches);
        return $matches[1] ?? '01';
    }

    private function importLessonContent($lessonNumber)
    {
        $paddedNumber = str_pad($lessonNumber, 2, '0', STR_PAD_LEFT);
        $lesson = null;

        // Import lesson metadata
        $lessonFile = resource_path("data/lessons/lesson-{$paddedNumber}.json");
        if (File::exists($lessonFile)) {
            $lessonData = json_decode(File::get($lessonFile), true);
            if (isset($lessonData['lesson'])) {
                $lesson = $this->createLesson($lessonData['lesson']);
                $this->command->info("✓ Lesson: {$lesson->title_english}");
            }
        }

        if (!$lesson) {
            $this->command->warn("No lesson data found for lesson {$lessonNumber}");
            return;
        }

        // Import vocabulary
        $vocabularyFile = resource_path("data/vocabulary/lesson-{$paddedNumber}.json");
        if (File::exists($vocabularyFile)) {
            $vocabularyData = json_decode(File::get($vocabularyFile), true);
            if (isset($vocabularyData['vocabulary'])) {
                foreach ($vocabularyData['vocabulary'] as $vocabData) {
                    $vocab = $this->createVocabulary($vocabData);
                    $this->command->info("  ✓ Vocabulary: {$vocab->word_english}");
                }
            }
        }

        // Import grammar points
        $grammarFile = resource_path("data/grammar/lesson-{$paddedNumber}.json");
        if (File::exists($grammarFile)) {
            $grammarData = json_decode(File::get($grammarFile), true);
            if (isset($grammarData['grammar_points'])) {
                foreach ($grammarData['grammar_points'] as $grammarPointData) {
                    $grammar = $this->createGrammarPoint($grammarPointData);
                    $this->command->info("  ✓ Grammar: {$grammar->name_english}");
                }
            }
        }

        // Import questions
        $questionsFile = resource_path("data/questions/lesson-{$paddedNumber}.json");
        if (File::exists($questionsFile)) {
            $questionsData = json_decode(File::get($questionsFile), true);
            if (isset($questionsData['questions'])) {
                foreach ($questionsData['questions'] as $questionData) {
                    $question = $this->createQuestion($questionData);
                    $this->command->info("  ✓ Question: {$question->type} ({$question->id})");
                }
            }
        }

        // Import tests
        $testsFile = resource_path("data/tests/lesson-{$paddedNumber}.json");
        if (File::exists($testsFile)) {
            $testsData = json_decode(File::get($testsFile), true);
            if (isset($testsData['tests'])) {
                foreach ($testsData['tests'] as $testData) {
                    $test = $this->createTest($testData);
                    $this->command->info("  ✓ Test: {$test->name}");
                }
            }
        }

        // Import worksheets
        $worksheetsFile = resource_path("data/worksheets/lesson-{$paddedNumber}.json");
        if (File::exists($worksheetsFile)) {
            $worksheetsData = json_decode(File::get($worksheetsFile), true);
            if (isset($worksheetsData['worksheets'])) {
                foreach ($worksheetsData['worksheets'] as $worksheetData) {
                    $worksheet = $this->createWorksheet($worksheetData);
                    $this->command->info("  ✓ Worksheet: {$worksheet->name}");
                }
            }
        }
    }

    private function createLesson($data)
    {
        return Lesson::updateOrCreate(
            ['id' => $data['id']], // Unique identifier
            [
                'chapter' => $data['chapter'],
                'title_japanese' => $data['title']['japanese'] ?? null,
                'title_furigana' => $data['title']['furigana'] ?? null,
                'title_english' => $data['title']['english'],
                'description' => $data['description'] ?? null,
                'difficulty' => $data['difficulty'] ?? 'beginner',
                'estimated_time_minutes' => $data['estimated_time_minutes'] ?? null,
                'prerequisites' => $data['prerequisites'] ?? [],
            ]
        );
    }

    private function createVocabulary($data)
    {
        return Vocabulary::updateOrCreate(
            ['id' => $data['id']], // Unique identifier
            [
                'lesson_id' => $data['lesson_id'],
                'word_japanese' => $data['word']['japanese'] ?? null,
                'word_furigana' => $data['word']['furigana'] ?? null,
                'word_english' => $data['word']['english'],
                'part_of_speech' => $data['part_of_speech'],
                'verb_type' => $data['verb_type'] ?? null,
                'adjective_type' => $data['adjective_type'] ?? null,
                'conjugations' => $data['conjugations'] ?? null,
                'pitch_accent' => $data['pitch_accent'] ?? null,
                'jlpt_level' => $data['jlpt_level'] ?? null,
                'frequency_rank' => $data['frequency_rank'] ?? null,
                'example_sentences' => $data['example_sentences'] ?? [],
                'audio_filename' => $data['audio']['filename'] ?? null,
                'audio_duration' => $data['audio']['duration'] ?? null,
                'audio_speaker' => $data['audio']['speaker'] ?? null,
                'mnemonics' => $data['mnemonics'] ?? null,
                'related_words' => $data['related_words'] ?? [],
                'tags' => $data['tags'] ?? [],
            ]
        );
    }

    private function createGrammarPoint($data)
    {
        return GrammarPoint::updateOrCreate(
            ['id' => $data['id']], // Unique identifier
            [
                'lesson_id' => $data['lesson_id'],
                'name_japanese' => $data['name']['japanese'] ?? null,
                'name_furigana' => $data['name']['furigana'] ?? null,
                'name_english' => $data['name']['english'],
                'pattern' => $data['pattern'],
                'usage' => $data['usage'],
                'explanation' => $data['explanation'] ?? null,
                'jlpt_level' => $data['jlpt_level'] ?? null,
                'examples' => $data['examples'] ?? [],
                'related_grammar' => $data['related_grammar'] ?? [],
            ]
        );
    }

    private function createQuestion($data)
    {
        return Question::updateOrCreate(
            ['id' => $data['id']], // Unique identifier
            [
                'lesson_id' => $data['lesson_id'],
                'type' => $data['type'],
                'difficulty' => $data['difficulty'] ?? 'beginner',
                'points' => $data['points'] ?? 1,
                'time_limit_seconds' => $data['time_limit_seconds'] ?? null,
                'question_japanese' => $data['question']['japanese'] ?? null,
                'question_furigana' => $data['question']['furigana'] ?? null,
                'question_english' => $data['question']['english'] ?? null,
                'context' => $data['context'] ?? null,
                'audio_filename' => $data['audio']['filename'] ?? null,
                'audio_duration' => $data['audio']['duration'] ?? null,
                'audio_speaker' => $data['audio']['speaker'] ?? null,
                'image_filename' => $data['image'] ?? null,
                'options' => $data['options'] ?? null,
                'correct_answer' => $data['correct_answer'],
                'explanation_japanese' => $data['explanation']['japanese'] ?? null,
                'explanation_furigana' => $data['explanation']['furigana'] ?? null,
                'explanation_english' => $data['explanation']['english'] ?? null,
                'hints' => $data['hints'] ?? [],
                'vocabulary_ids' => $data['vocabulary_ids'] ?? [],
                'grammar_ids' => $data['grammar_ids'] ?? [],
                'tags' => $data['tags'] ?? [],
            ]
        );
    }

    private function createTest($data)
    {
        return Test::updateOrCreate(
            ['id' => $data['id']], // Unique identifier
            [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'lesson_ids' => $data['lesson_ids'] ?? [],
                'question_ids' => $data['question_ids'] ?? [],
                'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
                'passing_score' => $data['passing_score'] ?? 70,
                'randomize_questions' => $data['randomize_questions'] ?? true,
                'randomize_options' => $data['randomize_options'] ?? true,
                'allow_retakes' => $data['allow_retakes'] ?? true,
                'show_results_immediately' => $data['show_results_immediately'] ?? false,
            ]
        );
    }

    private function createWorksheet($data)
    {
        return Worksheet::updateOrCreate(
            ['id' => $data['id']], // Unique identifier
            [
                'name' => $data['name'],
                'type' => $data['type'],
                'lesson_id' => $data['lesson_id'],
                'content_ids' => $data['content_ids'] ?? [],
                'template' => $data['template'] ?? null,
                'print_settings' => $data['print_settings'] ?? null,
            ]
        );
    }
}
