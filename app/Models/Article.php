<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\LaravelMarkdown\MarkdownRenderer;

class Article extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'lesson_id',
        'title',
        'subtitle',
        'covered_vocabulary_ids',
    ];

    protected $casts = [
        'covered_vocabulary_ids' => 'array',
    ];

    /**
     * Get the lesson this article belongs to
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the vocabulary items covered by this article
     */
    public function coveredVocabulary()
    {
        if (empty($this->covered_vocabulary_ids)) {
            return collect();
        }

        return Vocabulary::whereIn('id', $this->covered_vocabulary_ids)->get();
    }

    /**
     * Check if this article has a markdown content file
     */
    public function hasMarkdownContent(): bool
    {
        return file_exists($this->getMarkdownContentPath());
    }

    /**
     * Get the path to the markdown content file
     */
    public function getMarkdownContentPath(): string
    {
        return resource_path("data/notes/articles/{$this->id}.md");
    }

    /**
     * Get the rendered markdown content
     */
    public function getMarkdownContent(): ?string
    {
        if (!$this->hasMarkdownContent()) {
            return null;
        }

        $renderer = new MarkdownRenderer();

        $markdownContent = file_get_contents($this->getMarkdownContentPath());
        return $renderer->toHtml($markdownContent);
    }

    /**
     * Check if this article covers a specific vocabulary item
     */
    public function coversVocabulary(string $vocabularyId): bool
    {
        return in_array($vocabularyId, $this->covered_vocabulary_ids ?? []);
    }

    /**
     * Get articles that cover a specific vocabulary item
     */
    public static function coveringVocabulary(string $vocabularyId)
    {
        return self::whereJsonContains('covered_vocabulary_ids', $vocabularyId)->get();
    }
}
