<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exercise_id',
        'started_at',
        'completed_at',
        'score',
        'original_score',
        'total_points',
        'time_spent_seconds',
        'answers',
        'question_results',
        'manual_corrections',
        'last_corrected_at',
        'is_completed',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'integer',
        'original_score' => 'integer',
        'total_points' => 'integer',
        'time_spent_seconds' => 'integer',
        'answers' => 'array',
        'question_results' => 'array',
        'manual_corrections' => 'array',
        'last_corrected_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the user who attempted this exercise
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the exercise that was attempted
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id', 'id');
    }

    /**
     * Calculate the percentage score
     */
    public function getPercentageAttribute(): float
    {
        return $this->total_points > 0 ? round(($this->score / $this->total_points) * 100, 2) : 0;
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
     * Check if the exercise was passed (assume 70% passing grade for exercises)
     */
    public function getIsPassedAttribute(): bool
    {
        return $this->percentage >= 70;
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
        return $query->where('is_completed', true)->whereRaw('(score / total_points) * 100 >= 70');
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by exercise
     */
    public function scopeForExercise($query, $exerciseId)
    {
        return $query->where('exercise_id', $exerciseId);
    }

    /**
     * Get the latest attempt for a user and exercise
     */
    public static function getLatestAttempt($userId, $exerciseId): ?self
    {
        return static::where('user_id', $userId)
            ->where('exercise_id', $exerciseId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get the best attempt for a user and exercise
     */
    public static function getBestAttempt($userId, $exerciseId): ?self
    {
        return static::where('user_id', $userId)
            ->where('exercise_id', $exerciseId)
            ->where('is_completed', true)
            ->orderBy('score', 'desc')
            ->orderBy('time_spent_seconds', 'asc') // Prefer faster completion on ties
            ->first();
    }

    /**
     * Check if this attempt has manual corrections
     */
    public function hasManualCorrections(): bool
    {
        return !empty($this->manual_corrections);
    }

    /**
     * Get the number of manually corrected answers
     */
    public function getManualCorrectionCountAttribute(): int
    {
        return count($this->manual_corrections ?? []);
    }

    /**
     * Check if a specific question was manually corrected
     */
    public function isQuestionManuallyCorrected($questionId): bool
    {
        return in_array($questionId, $this->manual_corrections ?? []);
    }

    /**
     * Get the improvement from manual corrections
     */
    public function getManualImprovementAttribute(): int
    {
        return $this->score - $this->original_score;
    }

    /**
     * Get the original percentage before manual corrections
     */
    public function getOriginalPercentageAttribute(): float
    {
        return $this->total_points > 0 ? round(($this->original_score / $this->total_points) * 100, 2) : 0;
    }
}