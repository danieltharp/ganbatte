# Furigana Integration Guide

This document explains how to use the furigana system in your Japanese learning platform.

## Overview

The furigana system allows users to toggle small hiragana readings above kanji characters, making Japanese text more accessible to learners. The system supports the format `{漢字|かんじ}` which gets rendered as proper ruby text with CSS styling.

## Data Format

### JSON Structure
```json
{
  "word": {
    "japanese": "学生", 
    "furigana": "{学生|がく|せい}",
    "english": "student"
  }
}
```

### Furigana Patterns

**Single Reading:**
```json
"furigana": "{学生|がく|せい}"
```

**Character-by-Character:**
```json
"furigana": "{学|がく}{生|せい}"
```

**Mixed Text:**
```json
"furigana": "{私|わたし}は{学生|がく|せい}です。"
```

**Complex Example:**
```json
"furigana": "{第|だい}{一|いっ}{課|か}"
```

## Database Schema

Furigana fields have been added to all Japanese content tables:

```sql
-- lessons table
title_furigana TEXT NULL

-- vocabulary table  
word_furigana TEXT NULL

-- grammar_points table
name_furigana TEXT NULL

-- questions table
question_furigana TEXT NULL
explanation_furigana TEXT NULL
```

## Model Usage

### Accessing Furigana Data

```php
// Get furigana-enabled text with fallbacks
$vocabulary = Vocabulary::find(1);
echo $vocabulary->furigana_word; // Uses furigana if available, falls back to japanese

// Check if furigana is available
if ($vocabulary->hasFurigana()) {
    echo "This word has furigana readings";
}

// Direct access
echo $vocabulary->word_furigana; // Raw furigana string
```

### Model Helper Methods

All models with Japanese text now include:

```php
// Vocabulary model
$vocab->furigana_word;     // Display text with furigana
$vocab->hasFurigana();     // Boolean check

// Lesson model  
$lesson->furigana_title;   // Title with furigana
$lesson->hasFurigana();    // Boolean check

// Similar methods available for GrammarPoint and Question models
```

## Frontend Integration

### Blade Components

**Furigana Text Component:**
```blade
<x-furigana-text :text="$vocabulary->furigana_word" class="text-lg font-bold" />
```

**Toggle Button Component:**
```blade
<x-furigana-toggle text="Show/Hide Furigana" class="btn btn-primary" />
```

### Manual Usage

**In Blade Templates:**
```blade
<div class="japanese-text">
    {!! $vocabulary->furigana_word !!}
</div>

<button onclick="toggleFurigana()">Toggle Furigana</button>

@vite('resources/js/furigana.js')
```

**JavaScript Initialization:**
```javascript
// Auto-render on page load
document.addEventListener('DOMContentLoaded', function() {
    renderFurigana(document.body);
});

// Manual rendering of specific element
renderFurigana(document.querySelector('.lesson-content'));

// Toggle visibility
toggleFurigana();
```

## CSS Styling

The furigana system uses these CSS classes:

```css
/* Show furigana (default) */
ruby.furigana rt {
    visibility: visible;
}

/* Hide furigana when toggled off */
ruby.no-furigana rt {
    visibility: hidden !important;
}
```

### Custom Styling

You can customize the appearance:

```css
ruby.furigana {
    font-size: 1rem;
}

ruby.furigana rt {
    font-size: 0.6em;
    color: #666;
    font-weight: normal;
}

/* Hover effects */
ruby.furigana:hover rt {
    color: #333;
}
```

## Example Implementation

### Controller
```php
<?php

namespace App\Http\Controllers;

use App\Models\Lesson;

class LessonController extends Controller
{
    public function show(Lesson $lesson)
    {
        $lesson->load(['vocabulary', 'grammarPoints', 'questions']);
        return view('lessons.show', compact('lesson'));
    }
}
```

### View Template
```blade
<!DOCTYPE html>
<html>
<head>
    <title>{{ $lesson->title_english }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="lesson-container">
        <!-- Toggle Button -->
        <div class="controls">
            <x-furigana-toggle />
        </div>

        <!-- Lesson Title -->
        <h1><x-furigana-text :text="$lesson->furigana_title" /></h1>

        <!-- Vocabulary List -->
        <section class="vocabulary">
            <h2>Vocabulary</h2>
            @foreach($lesson->vocabulary as $vocab)
                <div class="vocab-item">
                    <div class="japanese">
                        <x-furigana-text :text="$vocab->furigana_word" class="vocab-word" />
                    </div>
                    <div class="english">{{ $vocab->word_english }}</div>
                </div>
            @endforeach
        </section>

        <!-- Grammar Points -->
        <section class="grammar">
            <h2>Grammar</h2>
            @foreach($lesson->grammarPoints as $grammar)
                <div class="grammar-item">
                    <h3><x-furigana-text :text="$grammar->name_furigana ?? $grammar->display_name" /></h3>
                    <p>{{ $grammar->explanation }}</p>
                    
                    @if($grammar->examples)
                        @foreach($grammar->examples as $example)
                            <div class="example">
                                <x-furigana-text :text="$example['sentence']['furigana'] ?? $example['sentence']['japanese']" />
                                <div class="translation">{{ $example['sentence']['english'] }}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endforeach
        </section>
    </div>

    @vite('resources/js/furigana.js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            renderFurigana(document.body);
        });
    </script>
</body>
</html>
```

## Data Import

When importing content, the seeder automatically handles furigana:

```php
// JSON data
{
  "vocabulary": [{
    "word": {
      "japanese": "学生",
      "furigana": "{学生|がく|せい}",
      "english": "student"
    }
  }]
}

// Seeder automatically imports
$vocabulary = $lesson->vocabulary()->create([
    'word_japanese' => $data['word']['japanese'],
    'word_furigana' => $data['word']['furigana'], // Handled automatically
    'word_english' => $data['word']['english'],
    // ... other fields
]);
```

## Best Practices

### Content Creation
1. **Consistent Format**: Always use `{kanji|reading}` format
2. **Character Splitting**: For multiple kanji, split appropriately: `{学|がく}{生|せい}`
3. **Mixed Content**: Include particles and kana as-is: `{私|わたし}は{学生|がく|せい}です。`
4. **Fallbacks**: Always provide both japanese and furigana versions

### Performance
1. **Lazy Loading**: Only render furigana when needed
2. **Caching**: Cache processed furigana HTML for frequently accessed content
3. **Selective Rendering**: Use `renderFurigana()` on specific elements rather than whole document

### User Experience
1. **Default State**: Consider whether furigana should be shown or hidden by default
2. **Persistence**: Remember user's toggle preference in localStorage
3. **Progressive Enhancement**: Ensure content is readable even without JavaScript

## Troubleshooting

### Common Issues

**Furigana not rendering:**
- Check that `furigana.js` is loaded
- Verify the text contains proper `{kanji|reading}` format
- Ensure `renderFurigana()` is called after DOM updates

**Toggle not working:**
- Confirm `toggleFurigana()` function is available
- Check CSS classes are properly applied
- Verify JavaScript console for errors

**Styling issues:**
- Ensure CSS is loaded and `ruby` elements are properly styled
- Check browser support for `<ruby>` elements
- Test responsive design on different screen sizes

### Browser Compatibility

The furigana system works in all modern browsers that support:
- `<ruby>` and `<rt>` HTML elements
- CSS `visibility` property
- ES6 JavaScript features

For older browsers, consider polyfills or fallback styling.

## Testing

Run the demo route to test furigana functionality:

```bash
php artisan migrate
php artisan db:seed
# Visit /demo in your browser
```

The demo page will show:
- Furigana toggle button
- Sample vocabulary with furigana
- Grammar examples with readings
- Interactive toggle functionality

This integration makes Japanese text more accessible while maintaining the authentic learning experience of gradually reducing reliance on furigana readings. 