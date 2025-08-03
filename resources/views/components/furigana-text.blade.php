@props(['text' => $slot->toHtml()])

@php
    // Convert furigana format from {漢字|ふり|がな} to proper HTML ruby tags
    // This matches the logic from resources/js/furigana.js
    $processedText = $text;
    
    // Handle furigana pattern: {kanji|furi|gana} 
    $processedText = preg_replace_callback('/\{([^|]+)((?:\|[^}]*)+)\}/', function($matches) {
        $kanjiText = $matches[1];
        $furiganaText = $matches[2];
        
        // Split furigana by | and remove first empty element, then reindex array
        $furiganaParts = array_values(array_filter(explode('|', $furiganaText)));
        
        // Safety check - make sure we have furigana parts
        if (empty($furiganaParts)) {
            return $kanjiText; // Return just the kanji if no furigana found
        }
        
        if (count($furiganaParts) === 1) {
            // Single furigana - keep kanji as one unit
            return "<ruby class=\"furigana\">{$kanjiText}<rt>{$furiganaParts[0]}</rt></ruby>";
        } else {
            // Multiple furigana parts - split kanji into individual characters
            $kanjiChars = mb_str_split($kanjiText);
            
            if (count($kanjiChars) === count($furiganaParts)) {
                // Create individual ruby elements for each character-furigana pair
                $rubyHTML = '<ruby class="furigana">';
                for ($i = 0; $i < count($kanjiChars); $i++) {
                    $rubyHTML .= $kanjiChars[$i] . '<rt>' . $furiganaParts[$i] . '</rt>';
                }
                $rubyHTML .= '</ruby>';
                return $rubyHTML;
            } else {
                // Fallback: if counts don't match, use single ruby with all furigana
                return "<ruby class=\"furigana\">{$kanjiText}<rt>" . implode('', $furiganaParts) . "</rt></ruby>";
            }
        }
    }, $processedText);
@endphp

<span class="furigana-text japanese-text" data-furigana-enabled="true">
    {!! $processedText !!}
</span> 