# Ganbatte v2 - Minna No Nihongo Companion Site

An e-learning platform for Japanese language learners, designed as a companion to the Minna No Nihongo textbook series. Built with Laravel and featuring comprehensive furigana support, interactive assessments, and printable worksheets.

## 🎌 Features

- **Structured Learning**: 50 lessons following Minna No Nihongo curriculum
- **Interactive Assessments**: Multiple question types with rich feedback
- **Furigana Support**: Toggle-able reading aids with `{kanji|reading}` format
- **Progress Tracking**: User progress and test attempt history
- **Printable Worksheets**: Handwriting practice and vocabulary review
- **Content Management**: Flexible JSON-based content system

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- Node.js & NPM
- SQLite (default) or MySQL/PostgreSQL

### Installation

1. **Clone and Install**
   ```bash
   git clone https://github.com/your-repo/ganbatte-v2.git
   cd ganbatte-v2
   composer install
   npm install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Build Assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

5. **Start Server**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to see the application!

## 📁 Content Structure

Content is organized by type for efficient management:

```
resources/data/
├── lessons/           # Lesson metadata
├── vocabulary/        # Vocabulary items  
├── grammar/           # Grammar points
├── questions/         # Assessment questions
├── tests/             # Test definitions
├── worksheets/        # Printable worksheets
└── templates/         # Empty templates
```

### Adding New Content

1. **Copy Templates**
   ```bash
   cp resources/data/templates/lesson-template.json resources/data/lessons/lesson-02.json
   cp resources/data/templates/vocabulary-template.json resources/data/vocabulary/lesson-02.json
   ```

2. **Edit Content**
   - Replace `XX` with lesson number (e.g., `02`)
   - Fill in Japanese text, English translations
   - Add furigana using `{kanji|reading}` format

3. **Import to Database**
   ```bash
   php artisan migrate:fresh --seed
   
   # Or for updates to existing content
   php artisan db:seed --class=LessonContentSeeder
   ```

## 🎯 Furigana Format

Two patterns for maximum flexibility:

- **Individual Kanji**: `{学生|がく|せい}` - each kanji gets its reading
- **Compound Reading**: `{時計|とけい}` - reading spans multiple kanji

Toggle furigana with the button or `toggleFurigana()` JavaScript function.

## 📚 Documentation

- **[Data Structure Guide](docs/data-structure-guide.md)** - Complete file organization
- **[Template Usage Guide](docs/template-usage-guide.md)** - Content creation workflow  
- **[Content Update Workflow](docs/content-update-workflow.md)** - Safe content updates with upsert
- **[Furigana Integration](docs/furigana-integration.md)** - Implementation details
- **[Option Explanations Guide](docs/option-explanations-guide.md)** - Question feedback system

## 🧪 Testing Content

### Demo Pages
- `/demo` - Interactive lesson with furigana
- `/lesson/{lesson}` - Specific lesson view

### Validation
```bash
# Check imported data
php artisan tinker
>>> \App\Models\Lesson::with(['vocabulary', 'questions'])->count();

# Test specific lesson
>>> $lesson = \App\Models\Lesson::with('vocabulary')->first();
>>> $lesson->vocabulary->count();
```

## 🏗️ Architecture

### Database Models
- **Lesson** - Lesson metadata and organization
- **Vocabulary** - Words with readings, audio, examples
- **GrammarPoint** - Patterns, explanations, examples
- **Question** - Assessments with rich feedback
- **Test** - Structured question collections
- **Worksheet** - Printable practice materials

### Frontend Components
- **Furigana Rendering** - `resources/js/furigana.js`
- **Toggle Button** - `resources/views/components/furigana-toggle.blade.php`
- **Text Display** - `resources/views/components/furigana-text.blade.php`

### Content Pipeline
1. **JSON Files** → Seeder → **Database** → **Views** → **User Interface**
2. Supports incremental updates and type-specific deployment
3. Maintains referential integrity across content types

## 🎮 Example Usage

### Content Creation
```json
// vocabulary/lesson-01.json
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
      "jlpt_level": "N5"
    }
  ]
}
```

### Frontend Display
```blade
<x-furigana-toggle />
<h1><x-furigana-text :text="$lesson->furigana_title" /></h1>
```

### JavaScript Integration
```javascript
// Render furigana on page load
document.addEventListener('DOMContentLoaded', function() {
    renderFurigana(document.body);
});
```

## 🤝 Contributing

1. **Content Contributors**: Use templates in `resources/data/templates/`
2. **Developers**: Follow Laravel conventions and maintain tests
3. **Translators**: Focus on accuracy and cultural context
4. **Reviewers**: Validate content before import

### Content Guidelines
- Use consistent furigana format
- Include example sentences for vocabulary
- Provide explanations for all question options
- Test import process before committing

## 📄 License

This project is open-source software licensed under the [MIT license](LICENSE).

## 🆘 Support

- **Documentation**: See `docs/` directory
- **Issues**: Submit GitHub issues for bugs
- **Content Questions**: Check template guides first
- **Development**: Follow Laravel documentation

---

**Ganbatte** (頑張って) - Do your best! 🎌✨
