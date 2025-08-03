<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'chapter',
        'title_japanese',
        'title_furigana',
        'title_english',
        'description',
        'difficulty',
        'estimated_time_minutes',
        'prerequisites',
    ];

    protected $casts = [
        'prerequisites' => 'array',
        'chapter' => 'integer',
        'estimated_time_minutes' => 'integer',
    ];

    /**
     * Get all vocabulary items for this lesson
     */
    public function vocabulary(): HasMany
    {
        return $this->hasMany(Vocabulary::class);
    }

    /**
     * Get all grammar points for this lesson
     */
    public function grammarPoints(): HasMany
    {
        return $this->hasMany(GrammarPoint::class);
    }

    /**
     * Get all questions for this lesson
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get all tests that include this lesson
     */
    public function tests()
    {
        return $this->belongsToMany(Test::class, 'test_lessons');
    }

    /**
     * Get all worksheets for this lesson
     */
    public function worksheets(): HasMany
    {
        return $this->hasMany(Worksheet::class);
    }

    /**
     * Get the full Japanese title with fallbacks
     */
    public function getJapaneseTitleAttribute(): string
    {
        return $this->title_japanese ?? $this->title_english;
    }

    /**
     * Get the title with furigana for display
     */
    public function getFuriganaTitleAttribute(): string
    {
        return $this->title_furigana ?? $this->title_japanese ?? $this->title_english;
    }

    /**
     * Check if this lesson title has furigana
     */
    public function hasFurigana(): bool
    {
        return !empty($this->title_furigana);
    }

    /**
     * Scope to filter by difficulty
     */
    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Scope to get lessons by chapter range
     */
    public function scopeByChapterRange($query, $start, $end)
    {
        return $query->whereBetween('chapter', [$start, $end]);
    }
} 