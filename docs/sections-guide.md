# Section Management Guide

This guide explains how to work with sections in the Ganbatte content management system.

## 📚 What are Sections?

**Sections** are guided study content that helps students work through textbook pages with audio and teacher explanations. They provide the context and guidance that textbooks assume a teacher would provide, making self-study possible.

## 🎯 Purpose & Design Philosophy

Sections solve a key problem: Japanese textbooks like Minna No Nihongo assume teacher guidance. Sections provide:

- **Audio accompaniment** for pronunciation and listening practice
- **Teacher explanations** that textbooks lack  
- **Learning guidance** for effective self-study
- **Progress tracking** to maintain motivation
- **Copyright compliance** by explaining purpose rather than reproducing content

## 🏗️ Section Structure

### Required Fields
```json
{
  "id": "mnn-01-sec001",              // Unique identifier
  "name": "Opening Dialogue: First Meeting",
  "lesson_id": "mnn-lesson-01",       // Associated lesson
  "page_number": 8,                   // Page in textbook
  "page_section": "top",              // Section of the page
  "section_type": "dialogue",         // Type of content
  "order_weight": 1,                  // Display order
  "purpose": "This dialogue shows...", // What students will learn
  "instructions": "1. Listen twice...", // How to study this section
  "audio_filename": "lesson-01-dialogue-01.mp3",
  "estimated_duration_minutes": 5,
  "prerequisites": [],                // Other section IDs needed first
  "related_vocabulary_ids": [...],    // Vocabulary used
  "related_grammar_ids": [...],       // Grammar covered
  "completion_trackable": true        // Can students mark complete?
}
```

### Field Details

- **`id`**: Pattern `mnn-{lesson}-sec{number}` (e.g., `mnn-01-sec001`)
- **`name`**: Human-readable title describing the content
- **`lesson_id`**: Foreign key to the lesson
- **`page_number`**: Integer page number in textbook
- **`page_section`**: Where on page (`full`, `top`, `middle`, `bottom`, `left`, `right`, `center`)
- **`section_type`**: Content category (see types below)
- **`order_weight`**: Integer for ordering (1, 2, 3...)
- **`purpose`**: Explains what students will learn and why
- **`instructions`**: Step-by-step study guidance
- **`audio_filename`**: MP3 file in `resources/mp3/` (optional)
- **`estimated_duration_minutes`**: How long to spend studying
- **`prerequisites`**: Array of section IDs that should be completed first
- **`related_vocabulary_ids`**: Vocabulary items used in this section
- **`related_grammar_ids`**: Grammar points covered
- **`completion_trackable`**: Whether students can mark this complete

## 📝 Section Types

### Available Types
- **`dialogue`** - Conversation practice with native audio
- **`vocabulary_intro`** - Introduction to new words with pronunciation
- **`grammar_intro`** - Grammar point explanations and examples
- **`listening`** - Pure listening comprehension exercises
- **`pronunciation`** - Sound and accent practice
- **`cultural_note`** - Cultural context and background
- **`review`** - Summary and consolidation activities
- **`practice`** - General practice activities

### Type Selection Guidelines
- **`dialogue`**: Natural conversations, role-play scenarios
- **`vocabulary_intro`**: New word introduction, pronunciation focus  
- **`grammar_intro`**: Grammar pattern explanation, example sentences
- **`listening`**: Audio-only comprehension, no visual support
- **`pronunciation`**: Phonetic practice, accent training
- **`cultural_note`**: Social/cultural context, background information
- **`review`**: Lesson summaries, consolidation activities
- **`practice`**: Mixed practice, application exercises

## 📂 File Organization

Section data is stored in JSON files:
```
resources/data/sections/
├── lesson-01.json
├── lesson-02.json
└── lesson-xx.json
```

Each file contains:
```json
{
  "sections": [
    { ... section 1 ... },
    { ... section 2 ... },
    { ... section 3 ... }
  ]
}
```

## 🔊 Audio Management

### Audio Files
- Store MP3 files in `resources/mp3/`
- Use descriptive filenames: `lesson-01-dialogue-01.mp3`
- Reference filename in `audio_filename` field

### Audio Properties
```php
$section = Section::find('mnn-01-sec001');
$section->hasAudio();           // true/false
$section->audio_path;           // Full file path
$section->audio_url;            // Public URL for frontend
```

## 🔄 Adding/Updating Sections

### 1. Edit JSON Files
Add or modify sections in the appropriate lesson file:

```json
{
  "sections": [
    {
      "id": "mnn-02-sec001",
      "name": "Demonstrative Pronouns",
      "lesson_id": "mnn-lesson-02",
      "page_number": 24,
      "page_section": "top",
      "section_type": "grammar_intro",
      "order_weight": 1,
      "purpose": "Learn to use これ, それ, あれ to point out objects and distinguish near/far relationships.",
      "instructions": "Study the example sentences carefully. Practice pointing to objects while saying the words. Audio includes natural pronunciation.",
      "audio_filename": "lesson-02-demonstratives.mp3",
      "estimated_duration_minutes": 8,
      "prerequisites": ["mnn-01-sec006"],
      "related_vocabulary_ids": ["mnn-02-001", "mnn-02-002"],
      "related_grammar_ids": ["mnn-02-grammar-001"],
      "completion_trackable": true
    }
  ]
}
```

### 2. Import Changes
Run the lessons:push command:
```bash
php artisan lessons:push
```

The system will automatically:
- Create new sections
- Update existing sections (by ID)
- Maintain referential integrity
- Show 🔊 icon for sections with audio

## 📊 Progress Tracking

### User Progress Model
Each user's section completion is tracked:
```php
// Check if user completed a section
$progress = UserSectionProgress::where('user_id', $userId)
    ->where('section_id', 'mnn-01-sec001')
    ->first();

if ($progress && $progress->isCompleted()) {
    echo "Section completed on: " . $progress->completed_at;
}

// Mark section complete
$progress = UserSectionProgress::firstOrCreate([
    'user_id' => $userId,
    'section_id' => 'mnn-01-sec001'
]);
$progress->markCompleted();
```

### Progress Queries
```php
// Get user's completed sections for a lesson
$completedSections = UserSectionProgress::forUser($userId)
    ->completed()
    ->whereHas('section', function($query) use ($lessonId) {
        $query->where('lesson_id', $lessonId);
    })
    ->get();

// Check prerequisites
$section = Section::find('mnn-01-sec003');
$prerequisitesComplete = $section->prerequisite_sections
    ->every(function($prereq) use ($userId) {
        return UserSectionProgress::forUser($userId)
            ->where('section_id', $prereq->id)
            ->completed()
            ->exists();
    });
```

## 🔗 Section Relationships

### Prerequisites
Sections can require other sections to be completed first:
```php
$section = Section::find('mnn-01-sec003');
$prerequisites = $section->prerequisite_sections; // Collection of Section models
```

### Related Content
Sections link to vocabulary and grammar:
```php
$section = Section::find('mnn-01-sec001');
$vocabulary = $section->vocabulary; // BelongsToMany relationship
$grammar = $section->grammarPoints; // BelongsToMany relationship
```

### Page Integration
Sections are organized by pages:
```php
// Get all sections for a page
$page = Page::find('page-mnn-lesson-01-textbook-8');
$sections = $page->sections; // HasMany relationship

// Get mixed content (sections + exercises) for a page
$content = $page->content; // Sorted by order_weight
```

## 🔍 Querying Sections

### Common Queries

**Get all sections for a lesson:**
```php
$sections = Section::byLesson('mnn-lesson-01')->ordered()->get();
```

**Get sections by type:**
```php
$dialogues = Section::byType('dialogue')->get();
$culturalNotes = Section::byType('cultural_note')->get();
```

**Get sections with audio:**
```php
$audioSections = Section::withAudio()->get();
```

**Get sections by page:**
```php
$pageSections = Section::byPage(12)
    ->byLesson('mnn-lesson-01')
    ->ordered()
    ->get();
```

**Get trackable sections:**
```php
$trackableSections = Section::trackable()->get();
```

## 🎯 Best Practices

### ID Naming Convention
- **Pattern**: `mnn-{lesson}-sec{number}`
- **Examples**: `mnn-01-sec001`, `mnn-01-sec002`, `mnn-25-sec003`
- **Consistency**: Use zero-padded lesson and section numbers

### Purpose & Instructions
Write clear, actionable content:

**Good Purpose:**
```json
{
  "purpose": "Master pronunciation of long vowels in Japanese, which can change word meanings completely. This skill is essential for clear communication."
}
```

**Good Instructions:**
```json
{
  "instructions": "1. Listen to each word pair without looking at text. 2. Repeat each word 3 times, focusing on vowel length. 3. Record yourself and compare to the audio. 4. Practice until you can clearly distinguish both sounds."
}
```

### Audio Guidelines
- **Quality**: Use clear, native speaker recordings
- **Naming**: `lesson-{number}-{type}-{sequence}.mp3`
- **Content**: Match exactly what students see in textbook
- **Speed**: Natural pace, not slowed down artificially

### Prerequisites
- Use sparingly - only when truly necessary
- Create logical learning paths
- Test prerequisite chains don't create loops

### Order Weights
- Start at 1 for each page
- Use gaps (1, 3, 5) for easy insertion later
- Consider logical study sequence

## 🚨 Important Considerations

### Copyright Compliance
- **Never reproduce textbook text** in purpose/instructions
- **Explain the purpose** of textbook content
- **Guide study approach** rather than replace textbook
- **Reference page numbers** so students use their textbooks

### Learning Design
- **Scaffold learning**: Easy to difficult progression
- **Provide context**: Why this matters for communication
- **Active engagement**: Clear action steps for students
- **Realistic timing**: Test duration estimates with real users

### Technical Considerations
- **File sizes**: Keep audio files reasonable (<5MB typically)
- **Dependencies**: Verify vocabulary/grammar IDs exist
- **Accessibility**: Consider users with hearing impairments

## 📊 Validation & Quality Assurance

### Content Validation
Check section integrity:
```bash
php artisan tinker
>>> $section = App\Models\Section::find('mnn-01-sec001');
>>> $missingVocab = collect($section->related_vocabulary_ids)
    ->diff(App\Models\Vocabulary::pluck('id'));
>>> if($missingVocab->isNotEmpty()) {
      echo "Missing vocabulary: " . $missingVocab->implode(', ');
    }
```

### Audio Validation
```php
// Check if audio files exist
$sections = Section::withAudio()->get();
$missingAudio = $sections->filter(function($section) {
    return !file_exists($section->audio_path);
});
```

## 🚀 Advanced Usage

### Custom Scopes
The Section model includes helpful scopes:
```php
// Get sections ordered by page and weight
Section::ordered()->get();

// Filter by type
Section::byType('dialogue')->get();

// Get trackable sections with audio
Section::trackable()->withAudio()->get();
```

### Page Integration
Work with the Page system:
```php
$page = Page::getOrCreatePage('mnn-lesson-01', 12, 'textbook');
$content = $page->content; // Mixed sections and exercises, ordered

// Auto-discover all pages for a lesson
$pages = Page::discoverPagesForLesson('mnn-lesson-01');
```

---

**Remember**: Sections bridge the gap between textbook content and effective self-study. They provide the teacher guidance that makes independent learning possible! 🎌🎧✨ 