<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GrammarPoint extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'lesson_id',
        'name_japanese',
        'name_furigana',
        'name_english',
        'pattern',
        'usage',
        'explanation',
        'jlpt_level',
        'examples',
        'related_grammar',
    ];

    protected $casts = [
        'examples' => 'array',
        'related_grammar' => 'array',
    ];

    /**
     * Get the lesson this grammar point belongs to
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get questions that test this grammar point
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_grammar');
    }

    /**
     * Get the primary name of the grammar point
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name_japanese ?? $this->name_english;
    }

    /**
     * Scope to filter by JLPT level
     */
    public function scopeByJlptLevel($query, $level)
    {
        return $query->where('jlpt_level', $level);
    }

    /**
     * Scope to search by pattern
     */
    public function scopeByPattern($query, $pattern)
    {
        return $query->where('pattern', 'like', "%{$pattern}%");
    }
} 