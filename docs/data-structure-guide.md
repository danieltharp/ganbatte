# Data Structure Guide - Content Type Organization

This guide explains the new file structure for Japanese learning content, organized by content type rather than by lesson. This approach provides better scalability, workflow efficiency, and incremental deployment capabilities.

## 📁 Directory Structure

```
resources/data/
├── lessons/           # Lesson metadata
│   ├── lesson-01.json
│   ├── lesson-02.json
│   └── ...
├── vocabulary/        # Vocabulary items
│   ├── lesson-01.json
│   ├── lesson-02.json
│   └── ...
├── grammar/           # Grammar points
│   ├── lesson-01.json
│   ├── lesson-02.json
│   └── ...
├── questions/         # Assessment questions
│   ├── lesson-01.json
│   ├── lesson-02.json
│   └── ...
├── tests/             # Test definitions
│   ├── lesson-01.json
│   ├── lesson-02.json
│   └── ...
├── worksheets/        # Printable worksheets
│   ├── lesson-01.json
│   ├── lesson-02.json
│   └── ...
└── templates/         # Empty templates for content creation
    ├── lesson-template.json
    ├── vocabulary-template.json
    ├── grammar-template.json
    ├── questions-template.json
    ├── tests-template.json
    └── worksheets-template.json
```

## 🎯 Benefits of Type-Based Organization

### 1. **Specialized Workflow**
- Content creators can focus on one type at a time
- Different team members can work on different content types
- Vocabulary specialists, grammar experts, and question writers can work independently

### 2. **Incremental Deployment**
- Upload vocabulary first, then grammar, then questions
- Build assessment system progressively
- Test individual components before full integration

### 3. **Scalability**
- Easy to manage 50+ lessons
- Clear separation of concerns
- Efficient version control and collaboration

### 4. **Maintenance**
- Update specific content types without affecting others
- Easier to spot patterns and inconsistencies within types
- Simpler bulk operations on specific content types

## 📄 File Formats

### Lessons (`lessons/lesson-XX.json`)
Contains lesson metadata and organization information.

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
    "description": "Introduction to basic Japanese greetings and self-introduction",
    "difficulty": "beginner",
    "estimated_time_minutes": 45,
    "prerequisites": []
  }
}
```

### Vocabulary (`vocabulary/lesson-XX.json`)
Contains all vocabulary items for a lesson.

```json
{
  "vocabulary": [
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
      "audio": {...},
      "tags": ["occupation", "school"],
      "include_in_kanji_worksheet": true
    }
  ]
}
```

### Grammar (`grammar/lesson-XX.json`)
Contains grammar points and patterns.

```json
{
  "grammar_points": [
    {
      "id": "mnn-01-grammar-001",
      "lesson_id": "mnn-lesson-01",
      "name": {
        "japanese": "Xは Yです",
        "english": "X is Y"
      },
      "pattern": "X は Y です",
      "usage": "Basic sentence structure for stating what something is",
      "explanation": "...",
      "examples": [...],
      "related_grammar": [...]
    }
  ]
}
```

### Questions (`questions/lesson-XX.json`)
Contains assessment questions with rich feedback.

```json
{
  "questions": [
    {
      "id": "mnn-01-q001",
      "lesson_id": "mnn-lesson-01",
      "type": "multiple_choice",
      "question": {
        "english": "How do you say 'student' in Japanese?"
      },
      "options": [
        {
          "japanese": "がくせい",
          "english": "gakusei",
          "explanation": "Correct! がくせい (gakusei) means 'student'."
        }
      ],
      "correct_answer": 0,
      "vocabulary_ids": ["mnn-01-001"],
      "tags": ["vocabulary", "occupation"]
    }
  ]
}
```

### Tests (`tests/lesson-XX.json`)
Contains structured test definitions.

```json
{
  "tests": [
    {
      "id": "mnn-lesson-01-test",
      "name": "Lesson 1 Completion Test",
      "description": "Test covering basic greetings and self-introduction",
      "lesson_ids": ["mnn-lesson-01"],
      "question_ids": ["mnn-01-q001", "mnn-01-q002"],
      "time_limit_minutes": 10,
      "passing_score": 70
    }
  ]
}
```

### Worksheets (`worksheets/lesson-XX.json`)
Contains printable worksheet definitions.

```json
{
  "worksheets": [
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
  ]
}
```

## 🔄 Content Creation Workflow

### Step 1: Plan Your Lesson
1. Create lesson metadata in `lessons/lesson-XX.json`
2. Define learning objectives and structure
3. Set difficulty and time estimates

### Step 2: Create Core Content
1. **Vocabulary First**: Add all words in `vocabulary/lesson-XX.json`
2. **Grammar Next**: Add patterns in `grammar/lesson-XX.json`
3. **Examples**: Include rich example sentences with furigana

### Step 3: Build Assessments
1. **Questions**: Create varied question types in `questions/lesson-XX.json`
2. **Tests**: Define structured assessments in `tests/lesson-XX.json`
3. **Worksheets**: Add practice materials in `worksheets/lesson-XX.json`

### Step 4: Link and Validate
1. Use consistent IDs to link content
2. Reference vocabulary and grammar in questions
3. Test import process: `php artisan migrate:fresh --seed`

## 🔗 ID Conventions and Linking

### Lesson IDs
- Format: `mnn-lesson-XX` (where XX is zero-padded)
- Example: `mnn-lesson-01`, `mnn-lesson-25`

### Content IDs
- **Vocabulary**: `mnn-XX-001`, `mnn-XX-002`
- **Grammar**: `mnn-XX-grammar-001`, `mnn-XX-grammar-002`
- **Questions**: `mnn-XX-q001`, `mnn-XX-q002`
- **Tests**: `mnn-lesson-XX-test`
- **Worksheets**: `mnn-XX-hiragana-ws`, `mnn-XX-vocab-ws`

### Cross-References
Questions can reference vocabulary and grammar:
```json
{
  "vocabulary_ids": ["mnn-01-001", "mnn-01-002"],
  "grammar_ids": ["mnn-01-grammar-001"]
}
```

## 📥 Import Process

The Laravel seeder automatically processes files by type:

1. **Scans** `lessons/` directory for lesson files
2. **Imports** lesson metadata first
3. **Processes** content types in order:
   - Vocabulary
   - Grammar
   - Questions  
   - Tests
   - Worksheets
4. **Links** relationships using IDs
5. **Reports** success/failure for each item

### Running Import
```bash
php artisan migrate:fresh --seed
```

### Import Output
```
Importing lesson content from JSON files...
Created lesson: Lesson 1
  - Added vocabulary: student
  - Added vocabulary: teacher
  - Added grammar: X is Y
  - Added question: multiple_choice
  - Added test: Lesson 1 Completion Test
  - Added worksheet: Lesson 1 Hiragana Practice
Lesson content import completed!
```

## 🔍 Troubleshooting

### Common Issues

**Missing Files**: Not all content types are required
- ✅ Only lessons are mandatory
- ✅ Other types are optional per lesson

**ID Mismatches**: Ensure consistent ID formats
- ❌ `lesson-01` ≠ `mnn-lesson-01`
- ✅ Use exact ID matching

**JSON Validation**: Check syntax before import
- Use JSON validators
- Check comma placement
- Verify quote marks

**Relationship Errors**: Verify referenced IDs exist
- Questions referencing non-existent vocabulary
- Tests referencing non-existent questions

### Validation Commands
```bash
# Test specific lesson import
php artisan tinker
>>> $seeder = new \Database\Seeders\LessonContentSeeder();
>>> $seeder->importLessonContent('01');

# Check imported data
>>> \App\Models\Lesson::with(['vocabulary', 'questions'])->first();
```

## 📋 Content Guidelines

### File Naming
- Use zero-padded numbers: `lesson-01.json`, not `lesson-1.json`
- Keep consistent across all content types
- Match lesson numbers exactly

### Content Quality
- **Vocabulary**: Include audio, examples, and tags
- **Grammar**: Provide clear patterns and multiple examples
- **Questions**: Add explanations for all options
- **Tests**: Balance difficulty and coverage
- **Worksheets**: Consider print formatting

### Furigana Format
- Individual kanji: `{学生|がく|せい}`
- Compound readings: `{時計|とけい}`
- See [Furigana Examples Guide](furigana-examples.md)

## 🚀 Next Steps

### For Content Creators
1. Copy templates from `templates/` directory
2. Start with lesson metadata
3. Build vocabulary systematically
4. Create comprehensive questions
5. Test import regularly

### For Developers
1. Extend seeder for new content types
2. Add validation rules
3. Create content management interface
4. Implement incremental updates

### For Teams
1. Assign content types to specialists
2. Use version control for collaboration
3. Review and validate before import
4. Maintain consistent quality standards

This structure scales efficiently from 1 to 50+ lessons while maintaining organization and enabling collaborative content creation! 🎌✨ 