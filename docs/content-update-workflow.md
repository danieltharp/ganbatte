# Content Update Workflow Guide

This guide explains how to safely update existing content using the upsert functionality built into the content management system.

## 🔄 Upsert Functionality

The seeder uses Laravel's `updateOrCreate()` method, which means:
- **Existing content** (matching the ID) will be **updated**
- **New content** will be **created**
- **No duplicate records** or constraint violations
- **Referential integrity** is maintained

## 🚀 Quick Start with `lessons:push`

The `php artisan lessons:push` command provides a user-friendly way to update your lesson content:

```bash
# Interactive update (recommended for first-time users)
php artisan lessons:push

# Preview what will be updated without making changes
php artisan lessons:push --dry-run

# Update without confirmation prompt (useful for scripts)
php artisan lessons:push --force
```

**Features:**
- ✅ **User-friendly output** with clear progress indicators
- ✅ **Dry-run mode** to preview changes before applying
- ✅ **Safety confirmation** to prevent accidental updates  
- ✅ **Error handling** with helpful error messages
- ✅ **File discovery** automatically finds all lesson content

## ✅ Safe Update Process

### 1. **Edit JSON Files**
Make changes directly to the JSON files in `resources/data/`:

```bash
# Edit any content type
nano resources/data/vocabulary/lesson-01.json
nano resources/data/questions/lesson-02.json
```

### 2. **Import Updates**
Run the content update command to apply changes:

```bash
# Recommended: Use the convenient alias command
php artisan lessons:push

# Preview changes without applying them
php artisan lessons:push --dry-run

# Skip confirmation prompt
php artisan lessons:push --force

# Alternative: Use the seeder directly
php artisan db:seed --class=LessonContentSeeder

# Or refresh everything
php artisan migrate:fresh --seed
```

### 3. **Verify Changes**
Check that updates were applied correctly:

```bash
php artisan tinker
>>> App\Models\Vocabulary::find('mnn-01-001')->word_english;
>>> App\Models\Question::find('mnn-01-q001')->options;
```

## 📝 Content Update Examples

### Updating Vocabulary
```json
// vocabulary/lesson-01.json
{
  "id": "mnn-01-001",
  "word": {
    "japanese": "学生",
    "english": "student (revised definition)"  // ← Updated
  },
  "tags": ["occupation", "school", "beginner"]  // ← Added tag
}
```

### Updating Questions
```json
// questions/lesson-01.json
{
  "id": "mnn-01-q001",
  "options": [
    {
      "japanese": "がくせい",
      "english": "gakusei",
      "explanation": "Correct! がくせい means student. Updated explanation with more detail."  // ← Enhanced
    }
  ]
}
```

### Adding New Content
```json
// vocabulary/lesson-01.json - Add new vocabulary item
{
  "vocabulary": [
    // ... existing items ...
    {
      "id": "mnn-01-006",  // ← New ID
      "lesson_id": "mnn-lesson-01",
      "word": {
        "japanese": "新しい言葉",
        "english": "new word"
      }
    }
  ]
}
```

## 🔑 ID-Based Updates

### How It Works
Each content item has a unique string ID:
- **Lessons**: `mnn-lesson-01`, `mnn-lesson-02`
- **Vocabulary**: `mnn-01-001`, `mnn-01-002`
- **Grammar**: `mnn-01-grammar-001`
- **Questions**: `mnn-01-q001`, `mnn-01-q002`
- **Tests**: `mnn-lesson-01-test`
- **Worksheets**: `mnn-01-hiragana-ws`
- **Exercises**: `mnn-01-ex001`, `mnn-01-ex002`

### Update Logic
```php
// This is what happens internally
Vocabulary::updateOrCreate(
    ['id' => 'mnn-01-001'],  // Find by this ID
    [...all_the_data...]     // Update with this data
);
```

### ID Management
- **Keep existing IDs** to update content
- **Create new IDs** to add content
- **Remove from JSON** to orphan content (manual cleanup needed)

## 📚 Exercise Content Type

**Exercises** are static content that comes directly from source textbooks, unlike worksheets and tests which may be dynamically generated. They represent specific exercises found on textbook pages.

### Exercise Structure
```json
{
  "id": "mnn-01-ex001",
  "name": "Exercise A",
  "lesson_id": "mnn-lesson-01",
  "page_number": 12,
  "book_reference": "textbook",     // "textbook" or "workbook"
  "order_weight": 1,                // Display order on the page
  "overview": "Practice basic greetings...",
  "question_ids": ["mnn-01-q001", "mnn-01-q002"]
}
```

### Exercise Features
- **Static content**: Comes directly from textbook, not generated
- **Page-based**: Tied to specific textbook/workbook pages
- **Ordered display**: Use `order_weight` for correct page ordering
- **Question references**: Links to existing questions in the system

## 🚨 Important Considerations

### Content Relationships
When updating content that's referenced by other content:

**✅ Safe Updates:**
- Changing text, translations, explanations
- Adding new fields or properties
- Updating metadata (difficulty, tags, etc.)

**⚠️ Be Careful With:**
- Changing content IDs (breaks references)
- Removing content that's referenced elsewhere
- Changing question types or structure

### Example: Updating Referenced Content
```json
// ✅ SAFE: Update vocabulary definition
{
  "id": "mnn-01-001",  // Keep same ID
  "word": {
    "english": "student (revised)"  // Update content
  }
}

// ❌ DANGEROUS: Change vocabulary ID
{
  "id": "mnn-01-001-NEW",  // Questions still reference mnn-01-001!
  "word": {
    "english": "student"
  }
}
```

## 📊 Monitoring Updates

### Before Updates
```bash
php artisan tinker
>>> App\Models\Vocabulary::count();  // Check current count
>>> App\Models\Question::find('mnn-01-q001')->updated_at;  // Check timestamp
```

### After Updates
```bash
php artisan lessons:push
# Check output for "✓" indicators showing what was processed

# Alternative: Use seeder directly
php artisan db:seed --class=LessonContentSeeder

php artisan tinker
>>> App\Models\Vocabulary::count();  // Should be same or higher
>>> App\Models\Question::find('mnn-01-q001')->updated_at;  // Should be newer
```

## 🔍 Troubleshooting Updates

### Common Issues

**1. Updates Not Applied**
- Check JSON syntax (use validator)
- Verify file paths are correct
- Ensure IDs match exactly

**2. References Broken**
- Check `vocabulary_ids` in questions
- Verify `lesson_id` matches lesson
- Confirm ID consistency across files

**3. Unexpected Duplicates**
- Should not happen with upsert
- If seen, check for ID typos
- Verify model primary key setup

### Validation Commands
```bash
# Check for orphaned references
php artisan tinker
>>> $question = App\Models\Question::with('vocabulary')->find('mnn-01-q001');
>>> $question->vocabulary->count();  // Should match vocabulary_ids count

# Verify content integrity  
>>> App\Models\Lesson::whereDoesntHave('vocabulary')->get();  // Lessons without vocab
>>> App\Models\Question::whereJsonLength('vocabulary_ids', '>', 0)
    ->whereDoesntHave('vocabulary')->get();  // Questions with broken vocab refs
```

## 📈 Best Practices

### 1. **Incremental Updates**
- Make small, focused changes
- Test updates frequently
- Keep backups of working JSON files

### 2. **Content Versioning**
Consider adding version comments:
```json
{
  "id": "mnn-01-001",
  "_version": "2.1",
  "_updated": "2025-07-27",
  "_changes": "Enhanced explanation with cultural context",
  "word": {
    "english": "student"
  }
}
```

### 3. **Batch Processing**
For large updates, process by content type:
```bash
# Update vocabulary first
edit resources/data/vocabulary/lesson-*.json
php artisan lessons:push

# Then update questions that reference vocabulary
edit resources/data/questions/lesson-*.json  
php artisan lessons:push
```

### 4. **Quality Assurance**
- Test content on demo pages after updates
- Verify furigana rendering still works
- Check question explanations display correctly
- Confirm tests include updated questions

## 🚀 Advanced Update Scenarios

### Bulk Updates Across Lessons
```bash
# Use command line tools for bulk changes
find resources/data/vocabulary/ -name "*.json" -exec sed -i 's/"beginner"/"elementary"/g' {} \;

# Then import all changes
php artisan lessons:push
```

### Content Migration
When restructuring content:
1. Export current data as backup
2. Update JSON structure
3. Test import with new structure
4. Verify all relationships intact

### Rolling Back Changes
Since upsert overwrites data:
1. Keep git history of JSON files
2. Revert JSON files to previous version
3. Re-run `php artisan lessons:push` to restore previous state

---

**Key Takeaway**: The upsert system makes content updates safe and reliable. You can confidently edit JSON files and re-import without fear of database integrity issues! 🎌✨ 