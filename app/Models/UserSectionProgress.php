<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSectionProgress extends Model
{
    use HasFactory;

    protected $table = 'user_section_progress';

    protected $fillable = [
        'user_id',
        'section_id',
        'completed_at',
        'attempts',
        'notes',
        'time_spent_minutes',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'attempts' => 'integer',
        'time_spent_minutes' => 'integer',
    ];

    /**
     * Get the user who completed this section
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the section that was completed
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Check if the section has been completed
     */
    public function isCompleted(): bool
    {
        return !is_null($this->completed_at);
    }

    /**
     * Mark the section as completed
     */
    public function markCompleted(): void
    {
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Reset completion status
     */
    public function resetCompletion(): void
    {
        $this->completed_at = null;
        $this->save();
    }

    /**
     * Scope to get completed progress records
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Scope to get incomplete progress records
     */
    public function scopeIncomplete($query)
    {
        return $query->whereNull('completed_at');
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
} 