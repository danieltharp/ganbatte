<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Worksheet extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'type',
        'lesson_id',
        'template',
        'content_ids',
    ];

    protected $casts = [
        'content_ids' => 'array',
    ];

    /**
     * Available worksheet types
     */
    const WORKSHEET_TYPES = [
        'kanji_practice',
        'hiragana_practice',
        'katakana_practice',
        'vocabulary_review',
        'grammar_exercises',
        'reading_comprehension',
    ];

    /**
     * Get the lesson this worksheet belongs to
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get vocabulary items included in this worksheet
     */
    public function vocabulary(): BelongsToMany
    {
        return $this->belongsToMany(Vocabulary::class, 'worksheet_vocabulary');
    }

    /**
     * Get grammar points included in this worksheet
     */
    public function grammarPoints(): BelongsToMany
    {
        return $this->belongsToMany(GrammarPoint::class, 'worksheet_grammar');
    }

    /**
     * Get questions included in this worksheet
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'worksheet_questions');
    }

    /**
     * Check if this is a handwriting practice worksheet
     */
    public function isHandwritingPractice(): bool
    {
        return in_array($this->type, [
            'kanji_practice',
            'hiragana_practice',
            'katakana_practice',
        ]);
    }



    /**
     * Scope to filter by worksheet type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get published worksheets
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
} 