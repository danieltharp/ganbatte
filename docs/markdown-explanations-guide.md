# Markdown Explanations for Grammar Points

This guide explains how to create and manage rich markdown explanations for grammar points in the Ganbatte Japanese learning application.

## Overview

Grammar points can now have rich markdown explanations instead of just plain text. The system automatically detects and renders markdown files while falling back to plain text explanations when markdown files don't exist.

## File Structure

Markdown explanation files are stored in:
```
resources/data/notes/grammar/{grammar_id}.md
```

Example: `resources/data/notes/grammar/mnn-01-grammar-001.md`

## Features

### 🎯 **Automatic Detection**
- The system automatically checks for markdown files
- Falls back to plain text explanations if no markdown file exists
- Visual indicator shows when markdown is being used

### 📝 **Rich Formatting Support**
- Headers, lists, code blocks, blockquotes
- **Bold** and *italic* text
- Tables and links
- Syntax highlighting for code
- Japanese text with proper typography

### 🛠️ **Helper Commands**

#### List Grammar Points Without Markdown
```bash
# List all grammar points without markdown explanations
php artisan grammar:list-without-explanations

# Filter by specific lesson
php artisan grammar:list-without-explanations --lesson=mnn-lesson-01
```

#### Create New Markdown Explanation
```bash
# Create a markdown explanation file with template
php artisan grammar:explanation mnn-01-grammar-001
```

## Workflow

### 1. **Identify Grammar Points Needing Rich Explanations**
```bash
php artisan grammar:list-without-explanations
```

### 2. **Create Markdown Explanation**
```bash
php artisan grammar:explanation {grammar_id}
```

### 3. **Edit the Generated Template**
The command creates a template with:
- Basic structure and headings
- Existing content from JSON data
- Placeholder sections for additional content
- Proper formatting examples

### 4. **View Results**
Visit `/grammar/{grammar_id}` to see the rendered explanation with:
- Rich typography and formatting
- "Rich Format" indicator badge
- Proper dark mode support

## Markdown Template Structure

Generated templates include:

```markdown
# Grammar Point Name

**Japanese**: 日本語パターン

## Pattern
```
Grammar pattern here
```

## Usage
Basic usage description...

## Explanation
Detailed explanation...

## Key Points
- **Point 1**: Important concept
- **Point 2**: Usage rule
- **Point 3**: Common mistake

## Formation
How to construct the pattern...

## Usage Notes
> **Important**: Critical information

## JLPT Level
**N5** - Level-specific notes

## Related Grammar
Links to related patterns...
```

## Best Practices

### ✅ **Do**
- Use clear, descriptive headings
- Include practical examples
- Add usage warnings in blockquotes
- Link to related grammar points
- Use code blocks for patterns
- Include cultural context when relevant

### ❌ **Avoid**
- Overly complex formatting
- Too much nesting
- Very long paragraphs
- Missing practical examples

## Technical Details

### Model Methods
- `hasMarkdownExplanation()` - Check if markdown file exists
- `getMarkdownExplanation()` - Get rendered markdown content  
- `getExplanationContent()` - Get best available explanation
- `isMarkdownExplanation()` - Check if using markdown format

### View Integration
The grammar show page automatically:
- Detects markdown explanations
- Renders with proper styling
- Shows format indicator
- Handles dark mode
- Applies Japanese typography

### Styling
Uses Tailwind Typography plugin with custom prose classes:
- Dark mode support
- Japanese font optimization
- Consistent spacing
- Proper heading hierarchy
- Code block highlighting

## Migration Strategy

1. **Gradual Migration**: Convert explanations as needed
2. **Priority Order**: Start with complex grammar points
3. **Template Generation**: Use commands for consistency
4. **Review Process**: Test rendering before finalizing

## Examples

See `resources/data/notes/grammar/mnn-01-grammar-001.md` for a complete example of a rich markdown explanation. 