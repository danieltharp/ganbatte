<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vocabulary extends Model
{
    use HasFactory;

    protected $table = 'vocabulary';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'lesson_id',
        'word_japanese',
        'word_furigana',
        'word_english',
        'part_of_speech',
        'verb_type',
        'adjective_type',
        'conjugations',
        'pitch_accent',
        'jlpt_level',
        'frequency_rank',
        'example_sentences',
        'audio_filename',
        'audio_duration',
        'audio_speaker',
        'mnemonics',
        'related_words',
        'tags',
    ];

    protected $casts = [
        'conjugations' => 'array',
        'example_sentences' => 'array',
        'related_words' => 'array',
        'tags' => 'array',
        'frequency_rank' => 'integer',
        'audio_duration' => 'float',
    ];

    /**
     * Get the lesson this vocabulary belongs to
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get questions that test this vocabulary
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_vocabulary');
    }

    /**
     * Get the primary Japanese form of the word
     */
    public function getJapaneseWordAttribute(): string
    {
        return $this->word_japanese ?? $this->word_english;
    }

    /**
     * Get the word with furigana for display
     */
    public function getFuriganaWordAttribute(): string
    {
        return $this->word_furigana ?? $this->word_japanese ?? $this->word_english;
    }

    /**
     * Check if this word has furigana
     */
    public function hasFurigana(): bool
    {
        return !empty($this->word_furigana);
    }

    /**
     * Check if this is a verb
     */
    public function isVerb(): bool
    {
        return $this->part_of_speech === 'verb';
    }

    /**
     * Check if this is an adjective
     */
    public function isAdjective(): bool
    {
        return $this->part_of_speech === 'adjective';
    }

    /**
     * Scope to filter by part of speech
     */
    public function scopeByPartOfSpeech($query, $pos)
    {
        return $query->where('part_of_speech', $pos);
    }

    /**
     * Scope to filter by JLPT level
     */
    public function scopeByJlptLevel($query, $level)
    {
        return $query->where('jlpt_level', $level);
    }

    /**
     * Scope to search by any Japanese form
     */
    public function scopeSearchJapanese($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('word_japanese', 'like', "%{$term}%");
        });
    }
} 