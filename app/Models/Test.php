<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'description',
        'time_limit_minutes',
        'passing_score',
        'randomize_questions',
        'randomize_options',
        'allow_retakes',
        'lesson_ids',
        'question_ids',
        'show_results_immediately',
    ];

    protected $casts = [
        'time_limit_minutes' => 'integer',
        'passing_score' => 'integer',
        'randomize_questions' => 'boolean',
        'lesson_ids' => 'array',
        'question_ids' => 'array',
        'randomize_options' => 'boolean',
        'allow_retakes' => 'boolean',
        'show_results_immediately' => 'boolean',
    ];

    /**
     * Get lessons included in this test
     */
    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'test_lessons');
    }

    /**
     * Get questions included in this test
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'test_questions')
                    ->withPivot('order', 'weight')
                    ->orderBy('pivot_order');
    }

    /**
     * Get test attempts by users
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    /**
     * Get the total possible points for this test
     */
    public function getTotalPointsAttribute(): int
    {
        return $this->questions->sum('points');
    }

    /**
     * Get the estimated duration in minutes
     */
    public function getEstimatedDurationAttribute(): int
    {
        return $this->questions->sum('time_limit_seconds') / 60;
    }

    /**
     * Scope to get published tests
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
} 