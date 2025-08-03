# Japanese Learning Content Data Format

This document outlines the comprehensive data format designed for your Minna No Nihongo companion e-learning website. The system supports storing vocabulary, grammar points, questions, tests, and worksheets with full Japanese language support.

## Overview

The data format is structured around lessons from the Minna No Nihongo textbook series, with each lesson containing:

- **Vocabulary items** with Japanese text, furigana, and English forms
- **Grammar points** with patterns, explanations, and examples
- **Questions** of various types for assessment
- **Tests** that combine questions into structured assessments
- **Worksheets** for printable practice materials

## Core Data Structure

### Japanese Text Format

All Japanese content follows a consistent structure supporting multiple writing systems:

```json
{
  "japanese": "学生",
  "furigana": "{学生|がく|せい}",
  "english": "student"
}
```

### Lesson Structure

Each lesson represents a chapter from Minna No Nihongo:

```json
{
  "lesson": {
    "id": "mnn-lesson-01",
    "chapter": 1,
      "title": {
    "japanese": "第一課",
    "furigana": "{第一課|だいいっか}",
    "english": "Lesson 1"
  },
    "description": "Introduction to basic Japanese greetings",
    "difficulty": "beginner",
    "estimated_time_minutes": 45,
    "prerequisites": []
  }
}
```

## Content Types

### 1. Vocabulary

Comprehensive vocabulary storage with linguistic information:

```json
{
  "id": "mnn-01-001",
  "lesson_id": "mnn-lesson-01",
      "word": {
      "japanese": "学生",
      "furigana": "{学生|がく|せい}",
      "english": "student"
    },
  "part_of_speech": "noun",
  "jlpt_level": "N5",
  "frequency_rank": 856,
  "example_sentences": [...],
  "audio": {
    "filename": "gakusei.mp3",
    "duration": 1.2,
    "speaker": "female"
  },
  "tags": ["occupation", "school"],
  "include_in_kanji_worksheet": true
}
```

**Key Features:**
- Support for verb conjugations and adjective types
- JLPT level classification
- Audio pronunciation files
- Example sentences with context
- Mnemonic devices and related words
- Kanji worksheet filtering via `include_in_kanji_worksheet` boolean (excludes hiragana/katakana-only words)

### 2. Grammar Points

Structured grammar explanations with examples:

```json
{
  "id": "mnn-01-grammar-001",
  "lesson_id": "mnn-lesson-01",
        "name": {
        "japanese": "Xは Yです",
        "english": "X is Y"
      },
  "pattern": "X は Y です",
  "usage": "Basic sentence structure for stating what something is",
  "explanation": "The topic marker は (wa) introduces what we're talking about...",
  "jlpt_level": "N5",
  "examples": [
    {
                "sentence": {
            "japanese": "私は学生です。",
            "furigana": "{私|わたし}は{学生|がく|せい}です。",
            "english": "I am a student."
          },
      "context": "Self-introduction"
    }
  ]
}
```

### 3. Questions

Flexible question system supporting multiple question types:

```json
{
  "id": "mnn-01-q001",
  "lesson_id": "mnn-lesson-01",
  "type": "multiple_choice",
  "difficulty": "beginner",
  "points": 5,
  "question": {
    "english": "How do you say 'student' in Japanese?"
  },
  "options": [...],
  "correct_answer": 0,
  "explanation": {
    "english": "がくsei (gakusei) means 'student'..."
  },
  "vocabulary_ids": ["mnn-01-001"],
  "tags": ["vocabulary", "occupation"]
}
```

**Supported Question Types:**
- `multiple_choice` - Multiple choice questions
- `fill_blank` - Fill in the blank
- `translation_j_to_e` - Japanese to English translation
- `translation_e_to_j` - English to Japanese translation
- `reading_comprehension` - Reading comprehension
- `listening` - Audio-based questions
- `handwriting` - Handwriting practice
- `sentence_ordering` - Sentence construction
- `particle_choice` - Particle selection
- `conjugation` - Verb/adjective conjugation

### 4. Tests

Structured assessments combining multiple questions:

```json
{
  "id": "mnn-lesson-01-test",
  "name": "Lesson 1 Completion Test",
  "description": "Test covering basic greetings and self-introduction",
  "lesson_ids": ["mnn-lesson-01"],
  "question_ids": ["mnn-01-q001", "mnn-01-q002"],
  "time_limit_minutes": 10,
  "passing_score": 70,
  "randomize_questions": true,
  "allow_retakes": true
}
```

### 5. Worksheets

Printable practice materials:

```json
{
  "id": "mnn-01-hiragana-ws",
  "name": "Lesson 1 Hiragana Practice",
  "type": "hiragana_practice",
  "lesson_id": "mnn-lesson-01",
  "content_ids": ["mnn-01-001", "mnn-01-002"],
  "template": "hiragana_grid_template",
  "print_settings": {
    "paper_size": "A4",
    "orientation": "portrait"
  }
}
```

**Worksheet Types:**
- `kanji_practice` - Kanji stroke order practice
- `hiragana_practice` - Hiragana writing practice
- `katakana_practice` - Katakana writing practice
- `vocabulary_review` - Vocabulary matching/review
- `grammar_exercises` - Grammar fill-in exercises
- `reading_comprehension` - Reading passages with questions

## Database Schema

The Laravel application includes comprehensive models and migrations:

### Models
- `Lesson` - Textbook chapters/lessons
- `Vocabulary` - Japanese vocabulary items
- `GrammarPoint` - Grammar explanations
- `Question` - Assessment questions
- `Test` - Structured assessments
- `Worksheet` - Printable materials
- `TestAttempt` - User test results
- `QuestionResponse` - Individual answer tracking

### Key Features
- Full Unicode support for Japanese text
- Flexible JSON storage for complex data structures
- Comprehensive indexing for performance
- Foreign key relationships with cascading deletes
- Progress tracking and analytics support

## Usage Examples

### Import Content from JSON

```php
use App\Models\Lesson;
use App\Models\Vocabulary;

// Load lesson data from JSON
$lessonData = json_decode(file_get_contents('lesson-01-example.json'), true);

// Create lesson
$lesson = Lesson::create([
    'chapter' => $lessonData['lesson']['chapter'],
    'title_japanese' => $lessonData['lesson']['title']['japanese'],
    'title_english' => $lessonData['lesson']['title']['english'],
    // ... other fields
]);

// Create vocabulary items  
foreach ($lessonData['vocabulary'] as $vocabData) {
    $lesson->vocabulary()->create([
        'word_japanese' => $vocabData['word']['japanese'],
        'word_english' => $vocabData['word']['english'],
        'part_of_speech' => $vocabData['part_of_speech'],
        // ... other fields
    ]);
}
```

### Query Examples

```php
// Get all N5 vocabulary from a lesson
$n5Vocab = Vocabulary::where('lesson_id', $lesson->id)
    ->byJlptLevel('N5')
    ->get();

// Find questions by type and difficulty
$beginnerQuestions = Question::byType('multiple_choice')
    ->byDifficulty('beginner')
    ->with(['vocabulary', 'grammarPoints'])
    ->get();

// Get user's test performance
$userStats = TestAttempt::where('user_id', $userId)
    ->completed()
    ->with('test')
    ->get();
```

## File Organization

```
resources/data/
├── schema/
│   └── content-schema.json          # JSON Schema definition
├── examples/
│   ├── lesson-01-example.json       # Sample lesson data
│   ├── lesson-02-example.json       # Additional lessons
│   └── audio/                       # Audio pronunciation files
└── templates/
    ├── worksheet-templates/         # Worksheet generation templates
    └── question-templates/          # Question format templates
```

## Best Practices

### Content Creation
1. **Consistent IDs**: Use descriptive, hierarchical IDs (`mnn-01-001`, `mnn-01-grammar-001`)
2. **Complete Japanese Forms**: Include all relevant writing systems for each entry
3. **Rich Context**: Provide example sentences and usage contexts
4. **Proper Tagging**: Use consistent tags for easy filtering and searching
5. **Audio Integration**: Include pronunciation files for vocabulary items

### Data Management
1. **Version Control**: Keep JSON files in version control for content management
2. **Validation**: Validate against the JSON schema before importing
3. **Incremental Updates**: Design import process to handle updates without duplication
4. **Backup Strategy**: Regular backups of both JSON files and database

### Performance Optimization
1. **Selective Loading**: Use Laravel's eager loading to avoid N+1 queries
2. **Caching**: Cache frequently accessed content like lesson lists
3. **Indexing**: Leverage database indexes for common query patterns
4. **Pagination**: Implement pagination for large content sets

## Next Steps

1. **Create Content Import Commands**: Laravel Artisan commands for batch importing
2. **Build Admin Interface**: Content management system for editing lessons
3. **Implement Search**: Full-text search across Japanese content
4. **Add Analytics**: Track learning progress and identify difficult concepts
5. **Extend Question Types**: Add more interactive question formats
6. **Mobile Optimization**: Ensure format works well for mobile learning apps

This data format provides a solid foundation for your Japanese learning platform, with room for growth as you add more features and content from the Minna No Nihongo series. 