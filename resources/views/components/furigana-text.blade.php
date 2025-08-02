@props(['text' => $slot->toHtml()])

@php
    // Convert furigana format from {漢字|かんじ} to proper HTML ruby tags
    $processedText = $text;
    
    // Handle furigana pattern: {kanji|furi|gana} or {kanji|furigana}
    $processedText = preg_replace_callback('/\{([^|]+)\|([^}]+)\}/', function($matches) {
        $kanji = $matches[1];
        $furigana = $matches[2];
        
        // Handle cases where there are multiple furigana parts separated by |
        $furiganaParts = explode('|', $furigana);
        
        if (count($furiganaParts) > 1) {
            // Multiple furigana parts for complex kanji
            return "<ruby>{$kanji}<rt>" . implode('', $furiganaParts) . "</rt></ruby>";
        } else {
            // Single furigana
            return "<ruby>{$kanji}<rt>{$furigana}</rt></ruby>";
        }
    }, $processedText);
@endphp

<span class="furigana-text japanese-text" data-furigana-enabled="true">
    {!! $processedText !!}
</span> 