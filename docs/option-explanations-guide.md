# Option Explanations Guide

This guide explains how to effectively use the optional `explanation` field in question options to provide valuable feedback to students.

## Overview

Each option in multiple choice questions can include an optional `explanation` field that provides immediate feedback when students select that answer. This creates a more interactive and educational experience.

## Basic Structure

```json
{
  "options": [
    {
      "english": "Answer text",
      "explanation": "Feedback for selecting this option"
    }
  ]
}
```

## Writing Effective Explanations

### For Correct Answers
Start with positive reinforcement and provide additional context:

```json
{
  "english": "gakusei",
  "explanation": "Correct! がくせい (gakusei) means 'student'. It's written with the kanji 学 (learning) + 生 (person)."
}
```

**Best Practices for Correct Answers:**
- Start with "Correct!" or "Right!"
- Explain why the answer is correct
- Provide additional learning context (etymology, usage, etc.)
- Reinforce the learning objective

### For Incorrect Answers
Be helpful and educational, not just corrective:

```json
{
  "english": "sensei",
  "explanation": "Incorrect. せんせい (sensei) means 'teacher', not 'student'. Remember: 先 (ahead) + 生 (person) = someone who goes ahead to guide others."
}
```

**Best Practices for Incorrect Answers:**
- Start with "Incorrect" or "Not quite"
- Explain what the chosen option actually means
- Provide a memory aid or distinction
- Guide toward the correct understanding

## Examples by Question Type

### Vocabulary Questions

**Question**: "How do you say 'student' in Japanese?"

```json
"options": [
          {
          "japanese": "がくせい",
          "english": "gakusei",
          "explanation": "Correct! がくせい (gakusei) means 'student'. The kanji 学生 combines 'learning' (学) and 'person' (生)."
        },
        {
          "japanese": "せんせい",
    "english": "sensei", 
    "explanation": "Incorrect. せんせい (sensei) means 'teacher'. Teachers are called 'sensei' as a sign of respect."
  },
          {
          "japanese": "かいしゃいん",
    "english": "kaishain",
    "explanation": "Incorrect. かいしゃいん (kaishain) means 'company employee' - someone who works for a business."
  },
          {
          "japanese": "いしゃ",
    "english": "isha",
    "explanation": "Incorrect. いしゃ (isha) means 'doctor' or 'physician'."
  }
]
```

### Grammar Questions

**Question**: "Which particle indicates the topic of a sentence?"

```json
"options": [
          {
          "japanese": "は",
    "explanation": "Correct! は (pronounced 'wa') is the topic particle. It introduces what the sentence is about."
  },
          {
          "japanese": "が", 
    "explanation": "Incorrect. が (ga) is the subject particle, which marks the grammatical subject, not the topic."
  },
  {
    "japanese": "を",
    "explanation": "Incorrect. を (wo/o) is the object particle, marking the direct object of an action."
  },
  {
    "japanese": "に",
    "explanation": "Incorrect. に (ni) indicates direction, time, or indirect objects, not topics."
  }
]
```

### Cultural Context Questions

**Question**: "When do you say はじめまして?"

```json
"options": [
  {
    "english": "When meeting someone for the first time",
    "explanation": "Correct! はじめまして is specifically used when meeting someone new. It literally means 'for the first time'."
  },
  {
    "english": "When saying goodbye",
    "explanation": "Incorrect. For goodbye, you'd say さようなら (sayounara) or また明日 (mata ashita) for 'see you tomorrow'."
  },
  {
    "english": "When entering someone's home",
    "explanation": "Incorrect. When entering someone's home, you say おじゃまします (ojamashimasu) meaning 'excuse me for intruding'."
  },
  {
    "english": "When apologizing",
    "explanation": "Incorrect. For apologies, use すみません (sumimasen) or ごめんなさい (gomen nasai)."
  }
]
```

## Advanced Explanation Techniques

### Connect to Previous Learning
```json
{
  "explanation": "Incorrect. Remember from Lesson 3 that ます endings make verbs polite. This is the casual form."
}
```

### Provide Memory Aids
```json
{
  "explanation": "Correct! Think: 'I' (私) starts with 'wa' sound, just like the particle は (wa)."
}
```

### Cultural Context
```json
{
  "explanation": "Incorrect. In Japanese culture, this phrase would be considered too direct. Japanese prefer indirect expressions."
}
```

### Common Mistakes
```json
{
  "explanation": "Incorrect. This is a common mistake! Many students confuse these because they sound similar."
}
```

## Content Guidelines

### Tone and Style
- **Encouraging**: Even incorrect answers should motivate learning
- **Clear**: Use simple, direct language
- **Educational**: Each explanation should teach something new
- **Consistent**: Maintain the same tone throughout your lesson

### Length Guidelines
- **Correct answers**: 15-30 words (provide context and reinforcement)
- **Incorrect answers**: 10-25 words (clarify and redirect)
- **Complex topics**: Up to 40 words if needed for clarity

### Avoid Common Pitfalls
- ❌ Don't just say "Wrong" or "Right"
- ❌ Don't repeat the question
- ❌ Don't make students feel bad for mistakes
- ❌ Don't provide overly complex explanations
- ✅ Always add educational value
- ✅ Connect to broader learning goals
- ✅ Encourage continued learning

## Technical Implementation

### JSON Structure
```json
{
  "options": [
    {
      "japanese": "漢字",
      "furigana": "{漢字|かんじ}",
      "english": "English",
      "explanation": "Feedback text here"
    }
  ]
}
```

### Optional Field
The `explanation` field is completely optional:
- Include it for enhanced learning experience
- Omit it for simpler questions
- Mix explained and non-explained options as needed

### Frontend Display
In your application, you can:
- Show explanations immediately after selection
- Display them in a popup or modal
- Use them for review after test completion
- Style correct/incorrect explanations differently

## Testing Your Explanations

Before publishing, check that your explanations:
- [ ] Are grammatically correct
- [ ] Provide educational value
- [ ] Use appropriate tone
- [ ] Are consistent with other content
- [ ] Help distinguish between similar options
- [ ] Connect to learning objectives
- [ ] Are appropriate length
- [ ] Use correct Japanese terminology

## Example Complete Question

Here's a well-crafted question with comprehensive explanations:

```json
{
  "id": "mnn-02-q001",
  "type": "multiple_choice",
  "question": {
    "english": "What do you say when meeting someone for the first time?"
  },
  "options": [
    {
      "japanese": "はじめまして",
      "english": "hajimemashite",
      "explanation": "Correct! はじめまして means 'nice to meet you' and is specifically used for first meetings. It shows politeness and respect."
    },
    {
      "japanese": "おはようございます",
      "english": "ohayou gozaimasu",
      "explanation": "Incorrect. おはようございます means 'good morning' and is a time-specific greeting, not for introductions."
    },
    {
      "japanese": "ありがとうございます",
      "english": "arigatou gozaimasu", 
      "explanation": "Incorrect. ありがとうございます means 'thank you'. While polite, it's not used for introductions."
    },
    {
      "japanese": "すみません",
      "english": "sumimasen",
      "explanation": "Incorrect. すみません means 'excuse me' or 'I'm sorry'. It's for getting attention or apologizing, not introductions."
    }
  ],
  "correct_answer": 0
}
```

This feature transforms simple multiple choice questions into rich learning experiences that provide immediate feedback and reinforce key concepts! 