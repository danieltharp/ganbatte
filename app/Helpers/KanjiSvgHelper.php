<?php

namespace App\Helpers;

use App\Models\Vocabulary;

class KanjiSvgHelper
{
    /**
     * Get the SVG file path for a given kanji character
     *
     * @param string $kanji Single kanji character
     * @return string|null Path to SVG file or null if not found
     */
    public static function getKanjiSvgPath(string $kanji): ?string
    {
        // Convert kanji character to Unicode codepoint (zero-padded to 5 digits)
        $codepoint = sprintf('%05x', mb_ord($kanji, 'UTF-8'));
        
        // Construct the SVG file path
        $svgPath = resource_path("svg/{$codepoint}.svg");
        
        return file_exists($svgPath) ? $svgPath : null;
    }

    /**
     * Get SVG content for a given kanji character
     *
     * @param string $kanji Single kanji character
     * @return string|null SVG content or null if not found
     */
    public static function getKanjiSvgContent(string $kanji): ?string
    {
        $path = self::getKanjiSvgPath($kanji);
        return $path ? file_get_contents($path) : null;
    }

    /**
     * Extract all kanji characters from a Japanese text string
     *
     * @param string $text Japanese text
     * @return array Array of unique kanji characters
     */
    public static function extractKanjiFromText(string $text): array
    {
        $kanji = [];
        
        // Iterate through each character in the string
        for ($i = 0; $i < mb_strlen($text, 'UTF-8'); $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            
            // Check if character is in CJK Unified Ideographs range (kanji)
            if (preg_match('/[\x{4e00}-\x{9faf}]/u', $char)) {
                $kanji[] = $char;
            }
        }
        
        // Return unique kanji only
        return array_unique($kanji);
    }

    /**
     * Extract kanji characters from a vocabulary word
     *
     * @param Vocabulary $vocabulary
     * @return array Array of unique kanji characters
     */
    public static function extractKanjiFromVocabulary(Vocabulary $vocabulary): array
    {
        $japanese = $vocabulary->word_japanese ?? '';
        return self::extractKanjiFromText($japanese);
    }

    /**
     * Get kanji data with SVG information for a vocabulary item
     *
     * @param Vocabulary $vocabulary
     * @return array Array with kanji, SVG paths, and availability info
     */
    public static function getKanjiDataForVocabulary(Vocabulary $vocabulary): array
    {
        $kanjiChars = self::extractKanjiFromVocabulary($vocabulary);
        $kanjiData = [];

        foreach ($kanjiChars as $kanji) {
            $svgPath = self::getKanjiSvgPath($kanji);
            $kanjiData[] = [
                'character' => $kanji,
                'codepoint' => sprintf('%05x', mb_ord($kanji, 'UTF-8')),
                'svg_path' => $svgPath,
                'svg_available' => $svgPath !== null,
                'svg_content' => $svgPath ? file_get_contents($svgPath) : null,
            ];
        }

        return $kanjiData;
    }

    /**
     * Check if a kanji character has an available SVG file
     *
     * @param string $kanji Single kanji character
     * @return bool True if SVG file exists, false otherwise
     */
    public static function hasKanjiSvg(string $kanji): bool
    {
        return self::getKanjiSvgPath($kanji) !== null;
    }

    /**
     * Get statistics about available kanji SVGs for a collection of vocabulary
     *
     * @param \Illuminate\Support\Collection $vocabularyCollection
     * @return array Statistics about kanji coverage
     */
    public static function getKanjiCoverageStats($vocabularyCollection): array
    {
        $totalKanji = 0;
        $availableKanji = 0;
        $missingKanji = [];

        foreach ($vocabularyCollection as $vocabulary) {
            $kanjiChars = self::extractKanjiFromVocabulary($vocabulary);
            $totalKanji += count($kanjiChars);

            foreach ($kanjiChars as $kanji) {
                if (self::hasKanjiSvg($kanji)) {
                    $availableKanji++;
                } else {
                    $missingKanji[] = $kanji;
                }
            }
        }

        return [
            'total_kanji' => $totalKanji,
            'available_kanji' => $availableKanji,
            'missing_kanji' => array_unique($missingKanji),
            'coverage_percentage' => $totalKanji > 0 ? round(($availableKanji / $totalKanji) * 100, 2) : 0,
        ];
    }

    /**
     * Clean and optimize SVG content for PDF generation
     *
     * @param string $svgContent Raw SVG content
     * @return string Optimized SVG content
     */
    public static function optimizeSvgForPdf(string $svgContent): string
    {
        // Extract just the essential parts for DomPDF compatibility
        $dom = new \DOMDocument();
        
        // Try to parse the SVG
        libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($svgContent);
        libxml_clear_errors();
        
        if (!$loaded) {
            // Fallback: return a simple text representation
            return '';
        }
        
        // Find the SVG element
        $svgElement = $dom->getElementsByTagName('svg')->item(0);
        if (!$svgElement) {
            return '';
        }
        
        // Create a simplified SVG
        $simpleSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="109" height="109" viewBox="0 0 109 109">';
        
        // Extract only the stroke paths (not the stroke numbers)
        $strokePaths = $dom->getElementsByTagName('path');
        foreach ($strokePaths as $path) {
            $d = $path->getAttribute('d');
            if (!empty($d)) {
                $simpleSvg .= '<path d="' . htmlspecialchars($d) . '" stroke="#000" stroke-width="3" fill="none" stroke-linecap="round"/>';
            }
        }
        
        $simpleSvg .= '</svg>';
        
        return $simpleSvg;
    }

    /**
     * Get a simple fallback representation of the kanji for PDF
     *
     * @param string $kanji Single kanji character
     * @return string Simple HTML representation
     */
    public static function getKanjiFallbackForPdf(string $kanji): string
    {
        return '<div style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 48px; font-family: serif;">' . 
               htmlspecialchars($kanji) . 
               '</div>';
    }

    /**
     * Convert Japanese text to HTML entities for better PDF compatibility
     *
     * @param string $text Japanese text
     * @return string HTML entity encoded text
     */
    public static function encodeJapaneseForPdf(string $text): string
    {
        // Convert Japanese characters to HTML entities
        $encoded = '';
        for ($i = 0; $i < mb_strlen($text, 'UTF-8'); $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            $codepoint = mb_ord($char, 'UTF-8');
            
            // If it's a Japanese character (Hiragana, Katakana, or CJK), encode it
            if (($codepoint >= 0x3040 && $codepoint <= 0x309F) ||  // Hiragana
                ($codepoint >= 0x30A0 && $codepoint <= 0x30FF) ||  // Katakana
                ($codepoint >= 0x4E00 && $codepoint <= 0x9FAF)) {  // CJK Unified Ideographs
                $encoded .= '&#' . $codepoint . ';';
            } else {
                $encoded .= $char;
            }
        }
        
        return $encoded;
    }

    /**
     * Extract stroke path data from SVG and create a visual representation using CSS
     *
     * @param string $svgContent Original SVG content
     * @return string HTML/CSS representation of strokes
     */
    public static function createStrokeGridForPdf(string $svgContent): string
    {
        // For now, create a simple visual grid representation
        // This is a fallback approach when SVG fails in DomPDF
        
        $strokeCount = substr_count($svgContent, '<path');
        
        $html = '<div style="position: relative; width: 100px; height: 100px; border: 2px solid #ddd; background: #f9f9f9;">';
        $html .= '<div style="position: absolute; top: 5px; left: 5px; font-size: 10px; color: #666;">';
        $html .= 'Strokes: ' . $strokeCount;
        $html .= '</div>';
        
        // Add some visual elements to represent stroke complexity
        for ($i = 0; $i < min($strokeCount, 8); $i++) {
            $top = 20 + ($i * 8);
            $left = 10 + ($i * 2);
            $html .= '<div style="position: absolute; top: ' . $top . 'px; left: ' . $left . 'px; width: ' . (20 + $i * 5) . 'px; height: 2px; background: #333; transform: rotate(' . ($i * 15) . 'deg);"></div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
