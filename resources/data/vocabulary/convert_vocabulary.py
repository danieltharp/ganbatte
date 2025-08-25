#!/usr/bin/env python3
"""
Convert Minna no Nihongo vocabulary TSV file to JSON format.

Usage: python convert_vocabulary.py <lesson_number> <input_tsv_file> <output_json_file>
Example: python convert_vocabulary.py 02 "Minna no Nihongo I-II__Book 1__Lesson 02.txt" lesson_02_vocabulary.json
"""

import sys
import json
import re
import argparse
from pathlib import Path


def parse_audio_filename(audio_field):
    """Extract audio filename from [sound:filename.mp3] format."""
    if not audio_field or audio_field.strip() == "":
        return ""
    
    # Find all [sound:...] patterns and take the first one
    matches = re.findall(r'\[sound:([^\]]+)\]', audio_field)
    if matches:
        return matches[0]  # Take the first match if multiple exist
    return ""


def clean_html_tags(text):
    """Remove HTML tags from text."""
    if not text:
        return ""
    # Remove HTML tags like <b>, </b>, etc.
    clean_text = re.sub(r'<[^>]+>', '', text)
    return clean_text.strip()


def transform_furigana_format(furigana_text):
    """
    Transform furigana from format 'kanji[reading] kanji[reading]' 
    to format '{kanjikanji|reading|reading}'.
    
    Examples:
    - '辞[じ] 書[しょ]' → '{辞書|じ|しょ}'
    - '本[ほん]' → '{本|ほん}'
    - '時計[とけい]' → '{時計|とけい}'
    """
    if not furigana_text or furigana_text.strip() == "":
        return ""
    
    # Handle cases where there are no brackets (just plain text)
    if '[' not in furigana_text:
        return furigana_text
    
    # Extract all kanji[reading] patterns
    pattern = r'([^[\s]+)\[([^\]]+)\]'
    matches = re.findall(pattern, furigana_text)
    
    if not matches:
        return furigana_text
    
    # Separate kanji and readings
    all_kanji = ''.join(match[0] for match in matches)
    all_readings = [match[1] for match in matches]
    
    # Create the new format: {kanji|reading1|reading2|...}
    if all_kanji and all_readings:
        return '{' + all_kanji + '|' + '|'.join(all_readings) + '}'
    
    return furigana_text


def parse_example_and_lesson(example_field):
    """Parse combined example sentence and lesson tag field."""
    if not example_field or example_field.strip() == "":
        return "", ""
    
    # The lesson tag is typically at the end (L02, L03, etc.)
    # Split by the last occurrence of 'L' followed by digits
    match = re.search(r'(.+?)(L\d+)$', example_field.strip())
    if match:
        example_sentence = match.group(1).strip()
        lesson_tag = match.group(2).strip()
        return clean_html_tags(example_sentence), lesson_tag
    else:
        # If no lesson tag found, treat the whole field as example sentence
        return clean_html_tags(example_field.strip()), ""


def parse_tsv_line(line, lesson_number):
    """Parse a single TSV line into vocabulary JSON structure."""
    # Skip empty lines and header lines
    line = line.strip()
    if not line or line.startswith('#'):
        return None
    
    # Split by tabs
    columns = line.split('\t')
    
    # Need at least 5 columns for basic data
    if len(columns) < 5:
        return None
    
    # Extract data from columns
    id_number = columns[0].strip()
    japanese = columns[1].strip()
    furigana = columns[2].strip()
    english = columns[3].strip()
    audio_field = columns[4].strip() if len(columns) > 4 else ""
    example_lesson_field = columns[5].strip() if len(columns) > 5 else ""
    
    # Parse example sentence and lesson tag
    example_sentence, lesson_tag = parse_example_and_lesson(example_lesson_field)
    
    # Skip if essential fields are empty
    if not id_number or not japanese or not english:
        return None
    
    # Pad ID to 3 digits
    padded_id = id_number.zfill(3)
    
    # Create vocabulary entry following the template structure
    vocab_entry = {
        "id": f"mnn-{lesson_number}-{padded_id}",
        "lesson_id": f"mnn-lesson-{lesson_number}",
        "word": {
            "japanese": japanese,
            "furigana": transform_furigana_format(furigana),
            "english": english
        },
        "part_of_speech": [],
        "verb_type": "",
        "adjective_type": "",
        "conjugations": {
            "past": {
                "japanese": "",
                "furigana": "",
                "english": ""
            },
            "negative": {
                "japanese": "",
                "furigana": "",
                "english": ""
            },
            "past_negative": {
                "japanese": "",
                "furigana": "",
                "english": ""
            },
            "te_form": {
                "japanese": "",
                "furigana": "",
                "english": ""
            }
        },
        "pitch_accent": "",
        "jlpt_level": "N5",
        "frequency_rank": 0,
        "example_sentences": [],
        "audio": {
            "filename": parse_audio_filename(audio_field),
            "duration": 0,
            "speaker": ""
        },
        "mnemonics": "",
        "related_words": [],
        "tags": [],
        "include_in_kanji_worksheet": False
    }
    
    # Add example sentence if it exists
    if example_sentence:
        vocab_entry["example_sentences"].append({
            "japanese": example_sentence,
            "furigana": "",
            "english": ""
        })
    
    return vocab_entry


def convert_tsv_to_json(lesson_number, input_file, output_file):
    """Convert TSV file to JSON vocabulary format."""
    vocabulary_entries = []
    
    # Read and process the TSV file
    try:
        with open(input_file, 'r', encoding='utf-8') as f:
            for line_num, line in enumerate(f, 1):
                try:
                    vocab_entry = parse_tsv_line(line, lesson_number)
                    if vocab_entry:
                        vocabulary_entries.append(vocab_entry)
                except Exception as e:
                    print(f"Warning: Error processing line {line_num}: {e}")
                    continue
        
        # Create final JSON structure
        json_data = {
            "vocabulary": vocabulary_entries
        }
        
        # Write to output file
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(json_data, f, indent=2, ensure_ascii=False)
        
        print(f"Successfully converted {len(vocabulary_entries)} vocabulary entries")
        print(f"Output written to: {output_file}")
        
    except FileNotFoundError:
        print(f"Error: Input file '{input_file}' not found")
        sys.exit(1)
    except Exception as e:
        print(f"Error: {e}")
        sys.exit(1)


def main():
    parser = argparse.ArgumentParser(
        description="Convert Minna no Nihongo vocabulary TSV to JSON format",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  python convert_vocabulary.py 02 "lesson_02.txt" "lesson_02_vocabulary.json"
  python convert_vocabulary.py 15 "input.txt" "output.json"
        """
    )
    
    parser.add_argument("lesson_number", 
                       help="Lesson number (e.g., '02', '15')")
    parser.add_argument("input_file", 
                       help="Input TSV file path")
    parser.add_argument("output_file", 
                       help="Output JSON file path")
    
    args = parser.parse_args()
    
    # Validate lesson number format
    lesson_number = args.lesson_number.zfill(2)  # Pad to 2 digits
    
    # Validate input file exists
    if not Path(args.input_file).exists():
        print(f"Error: Input file '{args.input_file}' does not exist")
        sys.exit(1)
    
    print(f"Converting lesson {lesson_number} vocabulary...")
    print(f"Input file: {args.input_file}")
    print(f"Output file: {args.output_file}")
    print()
    
    convert_tsv_to_json(lesson_number, args.input_file, args.output_file)


if __name__ == "__main__":
    main()
