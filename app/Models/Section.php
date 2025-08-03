<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'lesson_id',
        'page_number',
        'page_section',
        'section_type',
        'order_weight',
        'purpose',
        'instructions',
        'audio_filename',
        'estimated_duration_minutes',
        'prerequisites',
        'related_vocabulary_ids',
        'related_grammar_ids',
        'completion_trackable',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'order_weight' => 'integer',
        'estimated_duration_minutes' => 'integer',
        'prerequisites' => 'array',
        'related_vocabulary_ids' => 'array',
        'related_grammar_ids' => 'array',
        'completion_trackable' => 'boolean',
    ];

    /**
     * Available section types
     */
    const SECTION_TYPES = [
        'dialogue',
        'vocabulary_intro',
        'grammar_intro',
        'listening',
        'pronunciation',
        'cultural_note',
        'review',
        'practice',
    ];

    /**
     * Available page sections
     */
    const PAGE_SECTIONS = [
        'full',
        'top',
        'middle',
        'bottom',
        'left',
        'right',  
        'center',
    ];

    /**
     * Get the lesson this section belongs to
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get vocabulary items related to this section
     */
    public function vocabulary(): BelongsToMany
    {
        return $this->belongsToMany(Vocabulary::class, 'section_vocabulary');
    }

    /**
     * Get grammar points related to this section
     */
    public function grammarPoints(): BelongsToMany
    {
        return $this->belongsToMany(GrammarPoint::class, 'section_grammar');
    }

    /**
     * Get user progress records for this section
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserSectionProgress::class);
    }

    /**
     * Check if this section has audio
     */
    public function hasAudio(): bool
    {
        return !empty($this->audio_filename);
    }

    /**
     * Get the full audio file path
     */
    public function getAudioPathAttribute(): ?string
    {
        if (!$this->hasAudio()) {
            return null;
        }
        
        return resource_path('mp3/' . $this->audio_filename);
    }

    /**
     * Get the audio URL for the frontend
     */
    public function getAudioUrlAttribute(): ?string
    {
        if (!$this->hasAudio()) {
            return null;
        }
        
        return asset('audio/' . $this->audio_filename);
    }

    /**
     * Check if section allows completion tracking
     */
    public function isTrackable(): bool
    {
        return $this->completion_trackable;
    }

    /**
     * Get the display name with page reference
     */
    public function getDisplayNameAttribute(): string
    {
        $pageRef = "p.{$this->page_number}";
        if ($this->page_section !== 'full') {
            $pageRef .= " ({$this->page_section})";
        }
        return "{$this->name} ({$pageRef})";
    }

    /**
     * Scope to filter by section type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('section_type', $type);
    }

    /**
     * Scope to filter by lesson
     */
    public function scopeByLesson($query, $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    /**
     * Scope to filter by page
     */
    public function scopeByPage($query, $pageNumber)
    {
        return $query->where('page_number', $pageNumber);
    }

    /**
     * Scope to order by page and section weight
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('page_number')
                    ->orderBy('order_weight');
    }

    /**
     * Scope to get sections with audio
     */
    public function scopeWithAudio($query)
    {
        return $query->whereNotNull('audio_filename');
    }

    /**
     * Scope to get trackable sections
     */
    public function scopeTrackable($query)
    {
        return $query->where('completion_trackable', true);
    }

    /**
     * Get prerequisite sections
     */
    public function getPrerequisiteSectionsAttribute()
    {
        if (empty($this->prerequisites)) {
            return collect();
        }
        
        return static::whereIn('id', $this->prerequisites)->get();
    }
} 