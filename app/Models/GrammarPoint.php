<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\LaravelMarkdown\MarkdownRenderer;


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
     * Check if this grammar point has a markdown explanation file
     */
    public function hasMarkdownExplanation(): bool
    {
        return file_exists($this->getMarkdownExplanationPath());
    }

    /**
     * Get the path to the markdown explanation file
     */
    public function getMarkdownExplanationPath(): string
    {
        return resource_path("data/notes/grammar/{$this->id}.md");
    }

    /**
     * Get the rendered markdown explanation
     */
    public function getMarkdownExplanation(): ?string
    {
        if (!$this->hasMarkdownExplanation()) {
            return null;
        }

        $renderer = new MarkdownRenderer();

        $markdownContent = file_get_contents($this->getMarkdownExplanationPath());
        return $renderer->toHtml($markdownContent);
    }

    /**
     * Get the explanation content (either markdown or plain text)
     */
    public function getExplanationContent(): ?string
    {
        // Prefer markdown explanation if available
        if ($this->hasMarkdownExplanation()) {
            return $this->getMarkdownExplanation();
        }

        // Fall back to plain text explanation
        return $this->explanation ? nl2br(e($this->explanation)) : null;
    }

    /**
     * Check if the explanation is markdown format
     */
    public function isMarkdownExplanation(): bool
    {
        return $this->hasMarkdownExplanation();
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
