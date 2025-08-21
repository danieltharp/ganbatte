<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contribution extends Model
{
    protected $fillable = [
        'lesson_id',
        'user_id',
        'object_type',
        'object_id',
        'field_type',
        'contribution_text',
        'status',
        'reviewer_id',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime'
    ];

    /**
     * Status constants
     */
    const STATUS_NEW = 'new';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get the user who made this contribution
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who reviewed this contribution
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the lesson this contribution belongs to
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    /**
     * Scope to filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get new contributions
     */
    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /**
     * Scope to get accepted contributions
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope to get completed contributions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to filter by lesson
     */
    public function scopeForLesson($query, string $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    /**
     * Scope to filter by object type
     */
    public function scopeForObjectType($query, string $objectType)
    {
        return $query->where('object_type', $objectType);
    }

    /**
     * Mark contribution as accepted by reviewer
     */
    public function markAsAccepted(User $reviewer): bool
    {
        return $this->update([
            'status' => self::STATUS_ACCEPTED,
            'reviewer_id' => $reviewer->id,
            'reviewed_at' => now()
        ]);
        $this->user->incrementAcceptedContributions();
    }

    /**
     * Mark contribution as rejected by reviewer
     */
    public function markAsRejected(User $reviewer): bool
    {
        $this->user->incrementRejectedContributions();
        return $this->delete();
    }

    /**
     * Mark contribution as completed by developer
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED
        ]);
    }

    /**
     * Check if contribution is new
     */
    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    /**
     * Check if contribution is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Check if contribution is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
