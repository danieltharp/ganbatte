<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'test_id',
        'started_at',
        'completed_at',
        'score',
        'total_points',
        'time_spent_seconds',
        'answers',
        'is_completed',
        'is_passed',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'integer',
        'total_points' => 'integer',
        'time_spent_seconds' => 'integer',
        'answers' => 'array',
        'is_completed' => 'boolean',
        'is_passed' => 'boolean',
    ];

    /**
     * Get the user who took this test
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the test that was attempted
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Get individual question responses
     */
    public function responses(): HasMany
    {
        return $this->hasMany(QuestionResponse::class);
    }

    /**
     * Calculate the percentage score
     */
    public function getPercentageAttribute(): float
    {
        return $this->total_points > 0 ? ($this->score / $this->total_points) * 100 : 0;
    }

    /**
     * Get the duration in a human-readable format
     */
    public function getDurationAttribute(): string
    {
        $minutes = floor($this->time_spent_seconds / 60);
        $seconds = $this->time_spent_seconds % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Scope to get completed attempts
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope to get passed attempts
     */
    public function scopePassed($query)
    {
        return $query->where('is_passed', true);
    }
} 