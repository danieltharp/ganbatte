<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Page extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'lesson_id',
        'page_number',
        'book_reference',
        'title',
        'description',
        'learning_objectives',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'learning_objectives' => 'array',
    ];

    /**
     * Available book reference types
     */
    const BOOK_REFERENCES = [
        'textbook',
        'workbook',
    ];

    /**
     * Get the lesson this page belongs to
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get all sections on this page
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'page_number', 'page_number')
                    ->where('lesson_id', $this->lesson_id)
                    ->orderBy('order_weight');
    }

    /**
     * Get all exercises on this page
     */
    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class, 'page_number', 'page_number')
                    ->where('lesson_id', $this->lesson_id)
                    ->where('book_reference', $this->book_reference)
                    ->orderBy('order_weight');
    }

    /**
     * Get all content (sections and exercises) on this page in order
     */
    public function getContentAttribute(): Collection
    {
        $sections = $this->sections->map(function ($section) {
            return (object) [
                'type' => 'section',
                'id' => $section->id,
                'order_weight' => $section->order_weight,
                'page_section' => $section->page_section,
                'content' => $section,
            ];
        });

        $exercises = $this->exercises->map(function ($exercise) {
            return (object) [
                'type' => 'exercise',
                'id' => $exercise->id,
                'order_weight' => $exercise->order_weight,
                'page_section' => 'full', // Exercises typically span full sections
                'content' => $exercise,
            ];
        });

        return $sections->concat($exercises)->sortBy('order_weight');
    }

    /**
     * Get sections by page section
     */
    public function getSectionsByPageSection(string $pageSection): Collection
    {
        return $this->sections->where('page_section', $pageSection);
    }

    /**
     * Check if this page has audio content
     */
    public function hasAudio(): bool
    {
        return $this->sections->contains(function ($section) {
            return $section->hasAudio();
        });
    }

    /**
     * Get total estimated time for this page
     */
    public function getTotalEstimatedTimeAttribute(): int
    {
        return $this->sections->sum('estimated_duration_minutes');
    }

    /**
     * Get the display name with book reference
     */
    public function getDisplayNameAttribute(): string
    {
        $bookRef = ucfirst($this->book_reference);
        $title = $this->title ? ": {$this->title}" : '';
        return "{$bookRef} Page {$this->page_number}{$title}";
    }

    /**
     * Check if this page is from the textbook
     */
    public function isFromTextbook(): bool
    {
        return $this->book_reference === 'textbook';
    }

    /**
     * Check if this page is from the workbook
     */
    public function isFromWorkbook(): bool
    {
        return $this->book_reference === 'workbook';
    }

    /**
     * Scope to filter by book reference
     */
    public function scopeByBookReference($query, $bookReference)
    {
        return $query->where('book_reference', $bookReference);
    }

    /**
     * Scope to filter by lesson
     */
    public function scopeByLesson($query, $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    /**
     * Scope to order by page number
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('page_number');
    }

    /**
     * Create or get a page for the given parameters
     * This is a convenience method for auto-creating pages based on content
     */
    public static function getOrCreatePage(string $lessonId, int $pageNumber, string $bookReference): Page
    {
        $id = "page-{$lessonId}-{$bookReference}-{$pageNumber}";
        
        return static::updateOrCreate(
            ['id' => $id],
            [
                'lesson_id' => $lessonId,
                'page_number' => $pageNumber,
                'book_reference' => $bookReference,
                'title' => null,
                'description' => null,
                'learning_objectives' => [],
            ]
        );
    }

    /**
     * Auto-discover pages based on existing sections and exercises
     */
    public static function discoverPagesForLesson(string $lessonId): Collection
    {
        // Get all unique page numbers from sections and exercises
        $sectionPages = Section::where('lesson_id', $lessonId)
            ->select('page_number')
            ->distinct()
            ->pluck('page_number');

        $exercisePages = Exercise::where('lesson_id', $lessonId)
            ->select('page_number', 'book_reference')
            ->distinct()
            ->get()
            ->map(function ($exercise) {
                return (object) [
                    'page_number' => $exercise->page_number,
                    'book_reference' => $exercise->book_reference,
                ];
            });

        // Create pages for sections (assume textbook)
        $pages = collect();
        foreach ($sectionPages as $pageNumber) {
            $page = static::getOrCreatePage($lessonId, $pageNumber, 'textbook');
            $pages->push($page);
        }

        // Create pages for exercises
        foreach ($exercisePages as $exercisePage) {
            $page = static::getOrCreatePage(
                $lessonId, 
                $exercisePage->page_number, 
                $exercisePage->book_reference
            );
            if (!$pages->contains('id', $page->id)) {
                $pages->push($page);
            }
        }

        return $pages->sortBy('page_number');
    }
} 