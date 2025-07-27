# Lesson Template Usage Guide

This guide explains how to use the `lesson-template.json` file to create new lesson content for your Minna No Nihongo companion site.

## Quick Start

1. **Copy the template**: Copy `resources/data/templates/lesson-template.json` to `resources/data/examples/lesson-XX-example.json`
2. **Replace placeholders**: Change `XX` to your lesson number throughout the file
3. **Fill in content**: Replace empty strings with your lesson data
4. **Remove unused sections**: Delete any sections you don't need
5. **Import**: Use the seeder to import your completed lesson

## Template Structure

### Required Fields

Every lesson must have these minimum fields:

```json
{
  "lesson": {
    "id": "mnn-lesson-02",           // Unique lesson identifier
    "chapter": 2,                   // Chapter number (1-50)
    "title": {
      "english": "Lesson 2"        // At minimum, English title required
    }
  }
}
```

### Optional Sections

You can remove entire sections if not needed:
- `vocabulary` - Remove if no vocabulary items
- `grammar_points` - Remove if no grammar points  
- `questions` - Remove if no questions
- `tests` - Remove if no tests
- `worksheets` - Remove if no worksheets

## Field Reference

### Lesson Fields

| Field | Required | Type | Description |
|-------|----------|------|-------------|
| `id` | ✅ | string | Format: `mnn-lesson-XX` |
| `chapter` | ✅ | integer | 1-50 for Minna No Nihongo |
| `title.english` | ✅ | string | English lesson title |
| `title.japanese` | ❌ | string | Primary Japanese text (kanji, hiragana, etc.) |
| `title.furigana` | ❌ | string | Furigana format: `{漢字|かんじ}` |
| `description` | ❌ | string | Brief lesson description |
| `difficulty` | ❌ | enum | `beginner`, `elementary`, `intermediate`, `advanced` |
| `estimated_time_minutes` | ❌ | integer | Expected completion time |
| `prerequisites` | ❌ | array | Array of prerequisite lesson IDs |

### Vocabulary Fields

| Field | Required | Type | Description |
|-------|----------|------|-------------|
| `id` | ✅ | string | Format: `mnn-XX-001` |
| `lesson_id` | ✅ | string | Must match lesson ID |
| `word.english` | ✅ | string | English translation |
| `part_of_speech` | ✅ | enum | `noun`, `verb`, `adjective`, `adverb`, `particle`, `conjunction`, `interjection`, `counter`, `expression` |
| `word.japanese` | ❌ | string | Primary Japanese text (kanji, hiragana, etc.) |
| `word.furigana` | ❌ | string | Furigana: `{学生|がく|せい}` or `{時計|とけい}` |
| `verb_type` | ❌ | enum | `ichidan`, `godan`, `irregular`, `suru`, `kuru` (for verbs only) |
| `adjective_type` | ❌ | enum | `i_adjective`, `na_adjective` (for adjectives only) |
| `jlpt_level` | ❌ | enum | `N5`, `N4`, `N3`, `N2`, `N1` |
| `frequency_rank` | ❌ | integer | Word frequency ranking |
| `conjugations` | ❌ | object | Verb/adjective conjugations |
| `example_sentences` | ❌ | array | Example sentences using the word |
| `audio` | ❌ | object | Audio pronunciation file info |
| `tags` | ❌ | array | Categorization tags |

### Question Types

The template includes examples of all supported question types:

#### Multiple Choice
```json
{
  "type": "multiple_choice",
  "options": [
    {
      "english": "Option text",
      "explanation": "Feedback when this option is selected"
    }
  ],
  "correct_answer": 0  // Index of correct option
}
```

#### Translation
```json
{
  "type": "translation_e_to_j",  // or "translation_j_to_e"
  "correct_answer": ["answer1", "answer2", "answer3"]  // Multiple acceptable answers
}
```

#### Fill in the Blank
```json
{
  "type": "fill_blank",
  "question": {"english": "I am a student ______."},
  "correct_answer": ["です", "desu"]
}
```

#### Listening
```json
{
  "type": "listening",
  "audio": {
    "filename": "audio.mp3",
    "duration": 2.5,
    "speaker": "female"
  },
  "options": [
    {
      "english": "Option text",
      "explanation": "Why this answer is correct/incorrect"
    }
  ]
}
```

### Furigana Guidelines

#### Individual Kanji Readings
Use when each kanji has its own pronunciation:
```json
"furigana": "{学生|がく|せい}"  // 学=がく, 生=せい
"furigana": "{先生|せん|せい}"  // 先=せん, 生=せい
```

#### Compound Readings
Use when pronunciation spans multiple kanji:
```json
"furigana": "{時計|とけい}"      // Cannot split cleanly
"furigana": "{第一課|だいいっか}" // Lesson numbers
```

## Content Creation Workflow

### 1. Plan Your Lesson
- Determine vocabulary items (10-20 typical)
- Identify 2-4 key grammar points
- Plan 4-8 diverse questions
- Consider worksheet needs

### 2. Start with Vocabulary
Fill vocabulary items first, as they're referenced by questions:
```json
{
  "id": "mnn-02-001",
  "lesson_id": "mnn-lesson-02",
  "word": {
    "japanese": "本",
    "furigana": "{本|ほん}",  
    "english": "book"
  },
  "part_of_speech": "noun",
  "jlpt_level": "N5"
}
```

### 3. Add Grammar Points
```json
{
  "id": "mnn-02-grammar-001",
  "lesson_id": "mnn-lesson-02",
  "name": {"english": "これ/それ/あれ"},
  "pattern": "これ は X です",
  "usage": "Pointing to objects",
  "explanation": "これ (this), それ (that), あれ (that over there)"
}
```

### 4. Create Questions
Reference vocabulary and grammar by ID:
```json
{
  "vocabulary_ids": ["mnn-02-001", "mnn-02-002"],
  "grammar_ids": ["mnn-02-grammar-001"]
}
```

### 5. Configure Tests and Worksheets
Tests reference question IDs:
```json
{
  "question_ids": ["mnn-02-q001", "mnn-02-q002", "mnn-02-q003"]
}
```

## Best Practices

### IDs and References
- Use consistent ID patterns: `mnn-XX-001`, `mnn-XX-grammar-001`, `mnn-XX-q001`
- Replace all `XX` placeholders with your lesson number
- Ensure all references match exactly (case-sensitive)

### Japanese Text
- Include primary Japanese text and furigana when available
- Use proper furigana format for your content
- Provide English translations for all Japanese text

### Questions
- Mix question types for variety
- Include different difficulty levels within the lesson
- Reference relevant vocabulary and grammar items
- Provide clear explanations

### Audio Files
- Use descriptive filenames
- Include duration in seconds
- Specify speaker type (male/female/child)

### Content Organization
- Start simple, build complexity
- Group related vocabulary
- Introduce grammar before using in questions
- Create logical learning progression

## Validation Checklist

Before importing your lesson:

- [ ] All IDs are unique and follow naming convention
- [ ] All references (vocabulary_ids, grammar_ids, etc.) point to existing items
- [ ] Required fields are filled in
- [ ] Japanese text includes furigana where appropriate
- [ ] Question answers are in correct format for question type
- [ ] Audio files exist in specified location
- [ ] JSON syntax is valid (use JSON validator)

## Import Process

Once complete:
```bash
# Test the JSON syntax
cat resources/data/examples/lesson-02-example.json | jq .

# Import into database  
php artisan db:seed --class=LessonContentSeeder

# Or create custom seeder for your specific lesson
php artisan make:seeder Lesson02Seeder
```

## Common Mistakes

1. **ID Mismatches**: Make sure vocabulary_ids and grammar_ids reference actual items
2. **Wrong Question Format**: Each question type has specific requirements for correct_answer
3. **Missing Required Fields**: lesson.id, lesson.chapter, word.english, part_of_speech are mandatory
4. **Invalid JSON**: Use a JSON validator to check syntax
5. **Inconsistent Naming**: Don't mix naming patterns within the same lesson

This template provides a complete foundation for creating rich, interactive Japanese lessons that integrate seamlessly with your learning platform. 