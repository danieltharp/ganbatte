<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Exercise extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'lesson_id',
        'page_number',
        'book_reference',
        'order_weight',
        'overview',
        'question_ids',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'order_weight' => 'integer',
        'question_ids' => 'array',
    ];

    /**
     * Available book reference types
     */
    const BOOK_REFERENCES = [
        'textbook',
        'workbook',
    ];

    /**
     * Get the lesson this exercise belongs to
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get questions included in this exercise
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exercise_questions')
                    ->withPivot('order', 'weight')
                    ->orderBy('pivot_order');
    }

    /**
     * Get vocabulary items referenced by questions in this exercise
     */
    public function vocabulary(): BelongsToMany
    {
        return $this->belongsToMany(Vocabulary::class, 'exercise_vocabulary');
    }

    /**
     * Get grammar points referenced by questions in this exercise
     */
    public function grammarPoints(): BelongsToMany
    {
        return $this->belongsToMany(GrammarPoint::class, 'exercise_grammar');
    }

    /**
     * Check if this exercise is from the textbook
     */
    public function isFromTextbook(): bool
    {
        return $this->book_reference === 'textbook';
    }

    /**
     * Check if this exercise is from the workbook
     */
    public function isFromWorkbook(): bool
    {
        return $this->book_reference === 'workbook';
    }

    /**
     * Get the display name with page reference
     */
    public function getDisplayNameAttribute(): string
    {
        $bookRef = ucfirst($this->book_reference);
        return "{$this->name} ({$bookRef} p.{$this->page_number})";
    }

    /**
     * Scope to filter by book reference
     */
    public function scopeByBookReference($query, $bookReference)
    {
        return $query->where('book_reference', $bookReference);
    }

    /**
     * Scope to filter by lesson
     */
    public function scopeByLesson($query, $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    /**
     * Scope to order by page and order weight
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('page_number')
                    ->orderBy('order_weight');
    }
} 