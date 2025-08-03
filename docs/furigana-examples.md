# Furigana Format Examples

This document provides clear examples of when and how to format furigana for Japanese text.

## Core Principles

### Individual Kanji Readings
Use when each kanji has a distinct, separable reading:
```
Format: {kanji1kanji2|reading1|reading2}
```

### Compound Readings  
Use when the pronunciation spans multiple kanji and cannot be cleanly separated:
```
Format: {kanjicompound|wholereading}
```

## Detailed Examples

### ✅ Individual Kanji Readings

**学生 (student)**
- Format: `{学生|がく|せい}`
- Reasoning: 学 = がく, 生 = せい (each kanji has its own reading)

**先生 (teacher)**
- Format: `{先生|せん|せい}`  
- Reasoning: 先 = せん, 生 = せい (each kanji has its own reading)

**田中 (surname)**
- Format: `{田中|た|なか}`
- Reasoning: 田 = た, 中 = なか (each kanji has its own reading)

**新聞 (newspaper)**
- Format: `{新聞|しん|ぶん}`
- Reasoning: 新 = しん, 聞 = ぶん (each kanji has its own reading)

### ✅ Compound Readings

**時計 (clock/watch)**
- Format: `{時計|とけい}`
- Reasoning: "とけい" doesn't split cleanly (時 ≠ と, 計 ≠ けい)

**電話 (telephone)**
- Format: `{電話|でんわ}`
- Reasoning: Standard compound reading

**勉強 (study)**
- Format: `{勉強|べんきょう}`
- Reasoning: "べんきょう" is a compound reading

**友達 (friend)**
- Format: `{友達|ともだち}`
- Reasoning: "ともだち" spans both kanji as a unit

### ✅ Mixed Examples

**Sentence with both patterns:**
```json
"furigana": "{私|わたし}は{学生|がく|せい}です。{時計|とけい}を{持|も}っています。"
```

**Complex lesson title:**
```json
"furigana": "{第一課|だいいっか}"  // Compound reading for lesson number
```

**Name + title:**
```json
"furigana": "{田中|た|なか}{先生|せん|せい}です。"
```

## Decision Guide

### Use Individual Readings When:
- Each kanji retains its standard on'yomi or kun'yomi reading
- You can clearly map reading syllables to specific kanji
- Common in surnames, simple compound words

### Use Compound Readings When:
- The reading has undergone sound changes (rendaku, etc.)
- It's an irregular or special reading
- The pronunciation is treated as a single unit
- Common in set phrases, technical terms, place names

## Common Patterns

### Numbers + Counters
```json
"{一人|ひとり}"     // Compound (irregular reading)
"{二人|ふたり}"     // Compound (irregular reading)  
"{三人|さん|にん}"  // Individual (regular readings)
```

### Days of the Week
```json
"{月曜日|げつようび}"  // Compound
"{火曜日|かようび}"    // Compound
```

### Place Names (often compound)
```json
"{東京|とうきょう}"   // Compound
"{大阪|おおさか}"     // Compound
```

### Common Verbs
```json
"{勉強|べんきょう}する"  // Compound + hiragana
"{電話|でんわ}する"      // Compound + hiragana
```

## Technical Implementation

The JavaScript furigana renderer handles both patterns:

```javascript
// Individual: {学生|がく|せい} → <ruby>学<rt>がく</rt>生<rt>せい</rt></ruby>
// Compound: {時計|とけい} → <ruby>時計<rt>とけい</rt></ruby>
```

The pipe count determines the behavior:
- **2 pipes total** (1 separator): Compound reading
- **3+ pipes total** (2+ separators): Individual readings

## Best Practices

1. **Research Standard Readings**: Check dictionaries for standard pronunciation
2. **Consider Context**: Some words can use either pattern depending on emphasis
3. **Be Consistent**: Use the same pattern for the same word throughout your content
4. **Think Like a Learner**: Choose the pattern that best helps students understand the relationship between kanji and pronunciation

## Validation

Your furigana should:
- ✅ Have the correct number of pipes for the reading pattern
- ✅ Match the syllable count when using individual readings
- ✅ Use standard dictionary pronunciations
- ✅ Be consistent across your content

This format provides maximum flexibility while maintaining clarity for Japanese learners at all levels. 