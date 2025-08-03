<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_attempt_id',
        'question_id',
        'user_answer',
        'is_correct',
        'points_earned',
        'time_spent_seconds',
        'answered_at',
    ];

    protected $casts = [
        'user_answer' => 'json',
        'is_correct' => 'boolean',
        'points_earned' => 'integer',
        'time_spent_seconds' => 'integer',
        'answered_at' => 'datetime',
    ];

    /**
     * Get the test attempt this response belongs to
     */
    public function testAttempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class);
    }

    /**
     * Get the question that was answered
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Scope to get correct answers
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope to get incorrect answers
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }
} 