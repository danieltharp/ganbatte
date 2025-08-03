<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'lesson_id',
        'type',
        'difficulty',
        'points',
        'time_limit_seconds',
        'question_japanese',
        'question_furigana',
        'question_english',
        'context',
        'audio_filename',
        'audio_duration',
        'audio_speaker',
        'image_filename',
        'options',
        'correct_answer',
        'explanation_japanese',
        'explanation_furigana',
        'explanation_english',
        'hints',
        'vocabulary_ids',
        'grammar_ids',
        'tags',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'json', // Can be string, integer, or array
        'hints' => 'array',
        'vocabulary_ids' => 'array',
        'grammar_ids' => 'array',
        'tags' => 'array',
        'points' => 'integer',
        'time_limit_seconds' => 'integer',
        'audio_duration' => 'float',
    ];

    /**
     * Available question types
     */
    const QUESTION_TYPES = [
        'multiple_choice',
        'fill_blank',
        'translation_j_to_e',
        'translation_e_to_j',
        'reading_comprehension',
        'listening',
        'handwriting',
        'sentence_ordering',
        'particle_choice',
        'conjugation',
    ];

    /**
     * Get the lesson this question belongs to
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get vocabulary items tested by this question
     */
    public function vocabulary(): BelongsToMany
    {
        return $this->belongsToMany(Vocabulary::class, 'question_vocabulary');
    }

    /**
     * Get grammar points tested by this question
     */
    public function grammarPoints(): BelongsToMany
    {
        return $this->belongsToMany(GrammarPoint::class, 'question_grammar');
    }

    /**
     * Get tests that include this question
     */
    public function tests(): BelongsToMany
    {
        return $this->belongsToMany(Test::class, 'test_questions');
    }

    /**
     * Get the primary question text
     */
    public function getQuestionTextAttribute(): string
    {
        return $this->question_japanese ?? $this->question_english;
    }

    /**
     * Get the primary explanation text
     */
    public function getExplanationTextAttribute(): string
    {
        return $this->explanation_japanese ?? $this->explanation_english;
    }

    /**
     * Check if this is a multiple choice question
     */
    public function isMultipleChoice(): bool
    {
        return $this->type === 'multiple_choice';
    }

    /**
     * Check if this question has audio
     */
    public function hasAudio(): bool
    {
        return !empty($this->audio_filename);
    }

    /**
     * Check if this question has an image
     */
    public function hasImage(): bool
    {
        return !empty($this->image);
    }

    /**
     * Scope to filter by question type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by difficulty
     */
    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Scope to get questions with audio
     */
    public function scopeWithAudio($query)
    {
        return $query->whereNotNull('audio_filename');
    }
} 