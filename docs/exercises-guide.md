# Exercise Management Guide

This guide explains how to work with exercises in the Ganbatte content management system.

## 📚 What are Exercises?

**Exercises** are static content that comes directly from source textbooks (Minna No Nihongo textbook and workbook). Unlike worksheets and tests which might be dynamically generated in the future, exercises represent specific, numbered exercises found on textbook pages.

## 🏗️ Exercise Structure

### Required Fields
```json
{
  "id": "mnn-01-ex001",              // Unique identifier
  "name": "Exercise A",              // Friendly display name
  "lesson_id": "mnn-lesson-01",      // Associated lesson
  "page_number": 12,                 // Page in the book
  "book_reference": "textbook",      // "textbook" or "workbook"
  "order_weight": 1,                 // Display order on the page
  "overview": "Practice basic greetings and introductions...",
  "question_ids": [                  // References to questions
    "mnn-01-q001",
    "mnn-01-q002",
    "mnn-01-q003"
  ]
}
```

### Field Details

- **`id`**: Unique string identifier following pattern `mnn-{lesson}-ex{number}`
- **`name`**: Human-readable exercise name (e.g., "Exercise A", "Practice Problems 1")
- **`lesson_id`**: Foreign key reference to the lesson this exercise belongs to
- **`page_number`**: Integer page number where the exercise appears in the book
- **`book_reference`**: Either `"textbook"` or `"workbook"`
- **`order_weight`**: Integer for ordering exercises on the same page (1, 2, 3...)
- **`overview`**: Description of what the exercise covers and instructions
- **`question_ids`**: Array of question IDs that make up this exercise

## 📂 File Organization

Exercise data is stored in JSON files:
```
resources/data/exercises/
├── lesson-01.json
├── lesson-02.json
└── lesson-xx.json
```

Each file contains:
```json
{
  "exercises": [
    { ... exercise 1 ... },
    { ... exercise 2 ... },
    { ... exercise 3 ... }
  ]
}
```

## 🔄 Adding/Updating Exercises

### 1. Edit JSON Files
Add or modify exercises in the appropriate lesson file:

```json
{
  "exercises": [
    {
      "id": "mnn-02-ex001",
      "name": "Exercise A",
      "lesson_id": "mnn-lesson-02",
      "page_number": 24,
      "book_reference": "textbook",
      "order_weight": 1,
      "overview": "Practice using demonstrative pronouns. Point to objects and identify them using これ, それ, あれ.",
      "question_ids": [
        "mnn-02-q001",
        "mnn-02-q002"
      ]
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
- Create new exercises
- Update existing exercises (by ID)
- Maintain referential integrity

## 🔗 Exercise Relationships

### Questions
Exercises reference questions through the `question_ids` array:
```php
// Get all questions for an exercise
$exercise = Exercise::find('mnn-01-ex001');
$questions = Question::whereIn('id', $exercise->question_ids)->get();

// Or use the relationship (requires pivot table setup)
$questions = $exercise->questions;
```

### Lessons
Each exercise belongs to one lesson:
```php
$exercise = Exercise::find('mnn-01-ex001');
$lesson = $exercise->lesson;
```

### Page Ordering
Get exercises for a page, properly ordered:
```php
$exercises = Exercise::where('lesson_id', 'mnn-lesson-01')
    ->where('page_number', 12)
    ->where('book_reference', 'textbook')
    ->ordered()
    ->get();
```

## 🎯 Best Practices

### ID Naming Convention
- **Pattern**: `mnn-{lesson}-ex{number}`
- **Examples**: `mnn-01-ex001`, `mnn-01-ex002`, `mnn-25-ex003`
- **Consistency**: Use zero-padded lesson numbers and exercise numbers

### Book References
- Use `"textbook"` for main Minna No Nihongo textbook exercises
- Use `"workbook"` for workbook/practice book exercises

### Order Weights
- Start at 1 for each page
- Increment for each exercise on the same page
- Use gaps (1, 3, 5) if you might need to insert exercises later

### Overviews
Write clear, instructional overviews:
```json
{
  "overview": "Practice basic greetings and introductions. Complete the conversation using the vocabulary learned in this lesson. Pay attention to proper levels of politeness."
}
```

### Question References
- Only reference questions that exist in the system
- Questions should be logically grouped within the exercise
- Consider the order of questions in the `question_ids` array

## 🔍 Querying Exercises

### Common Queries

**Get all exercises for a lesson:**
```php
$exercises = Exercise::byLesson('mnn-lesson-01')->ordered()->get();
```

**Get textbook vs workbook exercises:**
```php
$textbookExercises = Exercise::byBookReference('textbook')->get();
$workbookExercises = Exercise::byBookReference('workbook')->get();
```

**Get exercises by page:**
```php
$pageExercises = Exercise::where('page_number', 12)
    ->where('book_reference', 'textbook')
    ->ordered()
    ->get();
```

**Display names with page info:**
```php
$exercises = Exercise::all();
foreach($exercises as $exercise) {
    echo $exercise->display_name; // "Exercise A (Textbook p.12)"
}
```

## 🚨 Important Considerations

### Exercise vs Worksheet vs Test

| Type | Purpose | Source | Generation |
|------|---------|---------|------------|
| **Exercise** | Direct textbook content | Static (textbook pages) | Never generated |
| **Worksheet** | Practice materials | May be generated | Can be dynamic |
| **Test** | Assessment | May be generated | Can be dynamic |

### Referential Integrity
- Always verify `question_ids` exist before creating exercises
- Be careful when deleting questions that are referenced by exercises
- Use the validation tools to check for broken references

### Page Management
- Multiple exercises can exist on the same page
- Use `order_weight` to control display order
- Consider that page numbers might vary between textbook editions

## 📊 Validation

Check exercise integrity:
```bash
php artisan tinker
>>> $exercise = App\Models\Exercise::find('mnn-01-ex001');
>>> $missingQuestions = collect($exercise->question_ids)
    ->diff(App\Models\Question::pluck('id'));
>>> if($missingQuestions->isNotEmpty()) {
      echo "Missing questions: " . $missingQuestions->implode(', ');
    }
```

## 🚀 Advanced Usage

### Custom Scopes
The Exercise model includes helpful scopes:
```php
// Get exercises ordered by page and weight
Exercise::ordered()->get();

// Filter by book reference
Exercise::byBookReference('textbook')->get();

// Filter by lesson
Exercise::byLesson('mnn-lesson-01')->get();
```

### Relationships
Access related content:
```php
$exercise = Exercise::with(['lesson', 'questions'])->find('mnn-01-ex001');
echo $exercise->lesson->title_english;
echo $exercise->questions->count() . " questions";
```

---

**Remember**: Exercises represent the static structure of textbook content, providing a bridge between lessons and the specific questions that students encounter in their books! 📖✨ 