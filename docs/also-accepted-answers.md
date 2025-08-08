# Alternative Accepted Answers for Vocabulary

## Overview
The `also_accepted` field allows vocabulary items to have multiple valid answers beyond the primary ones. This makes quizzes more fair and flexible by accepting common variations, synonyms, and alternative forms.

## JSON Structure
In vocabulary JSON files (`resources/data/vocabulary/lesson-*.json`), add an `also_accepted` object with `japanese` and `english` arrays:

```json
{
  "id": "mnn-01-001",
  "lesson_id": "mnn-lesson-01",
  "word": {
    "japanese": "私",
    "furigana": "{私|わたし}",
    "english": "I"
  },
  "part_of_speech": "noun",
  "also_accepted": {
    "english": ["me", "myself"],
    "japanese": ["わたし", "わたくし"]
  }
}
```

## How It Works

### Recognition Mode (Japanese → English)
- Primary answer: `word.english`
- Also accepted: All values in `also_accepted.english[]`
- Example: For "私", accepts "I", "me", or "myself"

### Recall Mode (English → Japanese)  
- Primary answer: `word.japanese`
- Also accepted: 
  - Hiragana reading from furigana (automatic)
  - All values in `also_accepted.japanese[]`
- Example: For "I", accepts "私", "わたし", or "わたくし"

## Guidelines

### When to Use
- **Synonyms**: Words with multiple English meanings (e.g., "先生" → "teacher", "instructor", "professor")
- **Variations**: Different forms of the same word (e.g., "私" → "わたし", "わたくし")
- **Common translations**: Multiple valid ways to express the same concept
- **Politeness levels**: Different formality levels of the same word

### Best Practices
1. Keep alternatives relevant and commonly used
2. Don't include misspellings or incorrect forms
3. For Japanese, include both kana and kanji variations where appropriate
4. For English, include both American and British spellings if different
5. Be consistent across lessons

## Database Migration
The feature requires the `also_accepted` column in the vocabulary table:
```bash
php artisan migrate
```

## Updating Content
After modifying vocabulary JSON files:
```bash
php artisan lessons:push
```

## Example Use Cases

### Multiple English Meanings
```json
"word": {
  "japanese": "先生",
  "furigana": "{先生|せん|せい}",
  "english": "teacher, instructor; (medical) doctor, medic"
},
"also_accepted": {
  "english": ["teacher", "instructor", "doctor", "professor"],
  "japanese": ["せんせい"]
}
```

### Formal/Informal Variations
```json
"word": {
  "japanese": "私",
  "furigana": "{私|わたし}",
  "english": "I"
},
"also_accepted": {
  "english": ["me", "myself"],
  "japanese": ["わたし", "わたくし", "ワタシ"]
}
```

### Regional Variations
```json
"word": {
  "japanese": "エレベーター",
  "english": "elevator"
},
"also_accepted": {
  "english": ["elevator", "lift"],
  "japanese": ["エレベータ"]
}
```

## Technical Details
- Field type: JSON column in database
- Model casting: Automatically cast to/from array in Laravel
- Normalization: Answers are normalized (lowercase, trimmed) before comparison
- Priority: Primary answer checked first, then alternatives
