<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+2&display=swap" rel="stylesheet">
    <title>Kanji Practice Worksheet - {{ $worksheet->name }}</title>
    <style>
        body {
            font-family: "M PLUS 2", 'courier', sans-serif;
            margin: 0;
            padding: 10px;
            font-size: 12px;
            color: #333;
        }
        
        .japanese-text {
            font-family: 'M PLUS 2', 'courier', sans-serif !important;
            font-size: 16px !important;
        }
        
        .kanji-character {
            font-family: 'M PLUS 2', 'courier', sans-serif !important;
            line-height: 1;
            position: relative;
            z-index: 10; /* Above practice grid lines */
        }
        
        .kanji-character.size-large {
            font-size: 40px !important;
        }
        
        .kanji-character.size-medium {
            font-size: 32px !important;
        }
        
        .kanji-character.size-small {
            font-size: 22px !important;
        }



        .kanji-grid {
            display: grid;
            grid-template-columns: repeat({{ $settings['grid_size'] ?? 6 }}, 1fr);
            gap: 15px;
            margin-bottom: 40px;
        }

        .kanji-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .kanji-row {
            width: 100%;
            margin-bottom: 15px;
            overflow: hidden; /* Clear floats */
        }
        
        .kanji-example {
            float: left;
            width: 120px;
            margin-right: 10px;
        }
        
        .kanji-practice-group {
            overflow: hidden; /* Contains floated children */
        }
        
        .kanji-practice-group .kanji-cell-container {
            float: left;
            margin-right: 5px;
            margin-bottom: 5px;
        }

        .vocabulary-header {
            font-size: 16px;
            margin-bottom: 8px;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
        }

        .vocabulary-info {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }

        .kanji-cell {
            border: 2px solid #333;
            position: relative;
            margin: 0 auto;
            background: white;
        }
        
        /* Size variations */
        .kanji-cell.size-large {
            width: 92px;
            height: 92px;
        }
        
        .kanji-cell.size-medium {
            width: 69px;
            height: 69px;
        }
        
        .kanji-cell.size-small {
            width: 46px;
            height: 46px;
        }

        .kanji-cell.practice {
            border: 1px solid #ccc;
        }

        .kanji-stroke-order {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            position: relative;
            z-index: 10; /* Above practice grid lines */
        }
        
        .kanji-stroke-order.size-large {
            width: 92px;
            height: 92px;
        }
        
        .kanji-stroke-order.size-medium {
            width: 69px;
            height: 69px;
        }
        
        .kanji-stroke-order.size-small {
            width: 46px;
            height: 46px;
        }

        .kanji-stroke-order svg {
            width: 100% !important;
            height: 100% !important;
            max-width: 100%;
            max-height: 100%;
        }

        .practice-grid {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1; /* Background layer for guide lines */
        }

        .practice-grid::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ddd;
            transform: translateY(-50%);
            z-index: 1; /* Same layer as parent - behind SVG content */
        }

        .practice-grid::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 1px;
            background: #ddd;
            transform: translateX(-50%);
            z-index: 1; /* Same layer as parent - behind SVG content */
        }

        .kanji-reading {
            text-align: center;
            font-size: 10px;
            margin-top: 5px;
            color: #666;
        }

        .page-break {
            page-break-before: always;
        }

        .worksheet-footer {
            position: fixed;
            bottom: 0mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            font-size: 12px;
            color: #999;
            background: white;
            z-index: 999;
        }

        @page {
            margin: 15mm;
            size: {{ $settings['paper_size'] ?? 'A4' }} {{ $settings['orientation'] ?? 'portrait' }};
        }

        /* Stroke order styling */
        .stroke-paths {
            stroke: #333;
            stroke-width: 3;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .stroke-numbers {
            font-size: 8px;
            fill: #666;
        }
    </style>
</head>
<body>


    @php
        // Precise DomPDF calculations at 96 DPI
        $paperSize = $settings['paper_size'] ?? 'A4';

        // Calculate optimal page breaks based on content size and practice size
        $gridSize = $settings['grid_size'] ?? 6;
        $isLandscape = ($settings['orientation'] ?? 'portrait') === 'landscape';
        $practiceSize = $settings['practice_size'] ?? 'large';
        
        // Cell dimensions for different sizes (matching JavaScript calculation)
        // Account for Letter paper being wider than A4 in landscape mode
        $gridsPerRowLandscape = [
            'large' => ($paperSize === 'Letter') ? 8 : 7,
            'medium' => ($paperSize === 'Letter') ? 10 : 9,
            'small' => ($paperSize === 'Letter') ? 15 : 13,
        ];
        
        $cellSizes = [
            'large' => ['width' => 92, 'height' => 92, 'grids_per_row' => $isLandscape ? $gridsPerRowLandscape['large'] : 5],
            'medium' => ['width' => 69, 'height' => 69, 'grids_per_row' => $isLandscape ? $gridsPerRowLandscape['medium'] : 7], 
            'small' => ['width' => 46, 'height' => 46, 'grids_per_row' => $isLandscape ? $gridsPerRowLandscape['small'] : 10],
        ];
        
        // Mixed mode uses full rows of each size
        $mixedTotalGrids = 0;
        if ($practiceSize === 'mixed') {
            $largeGridsPerRow = $isLandscape ? $gridsPerRowLandscape['large'] : 5;
            $mediumGridsPerRow = $isLandscape ? $gridsPerRowLandscape['medium'] : 7;
            $smallGridsPerRow = $isLandscape ? $gridsPerRowLandscape['small'] : 10;
            $mixedTotalGrids = $largeGridsPerRow + $mediumGridsPerRow + $smallGridsPerRow;
        }
        
        
        // Paper dimensions in inches, then convert to pixels at 96 DPI
        $paperDimensions = [
            'A4' => ['width' => 8.27, 'height' => 11.69], // 210×297mm
            'Letter' => ['width' => 8.5, 'height' => 11.0],
            'Legal' => ['width' => 8.5, 'height' => 14.0]
        ];
        
        $dpi = 96;
        $marginInches = 15 / 25.4; // 15mm margins converted to inches (0.591")
        $footerMarginInches = 20 / 25.4; // 20mm bottom margin for footer (0.787")
        
        $paperWidth = $paperDimensions[$paperSize]['width'] * $dpi;
        $paperHeight = $paperDimensions[$paperSize]['height'] * $dpi;
        $marginPixels = $marginInches * $dpi; // ~57 pixels
        $footerMarginPixels = $footerMarginInches * $dpi; // ~95 pixels
        
        // Usable area after margins (asymmetric - larger bottom margin for footer)
        $usableWidth = $paperWidth - (2 * $marginPixels);
        $usableHeight = $paperHeight - $marginPixels - $footerMarginPixels;
        
        // Swap dimensions for landscape
        if ($isLandscape) {
            [$usableWidth, $usableHeight] = [$usableHeight, $usableWidth];
        }
        
        // Component heights (measured in pixels)
        $kanjiHeaderHeight = 55; // vocabulary header + info
        $footerHeight = 25; // footer space
        $availableContentHeight = $usableHeight - $footerHeight;
        
        if ($practiceSize === 'mixed') {
            // Mixed mode: full rows of each size (large + medium + small)
            $practiceRows = ceil($gridSize / $mixedTotalGrids); // How many practice rows were requested
            $practiceRowHeight = ($practiceRows * 92) + ($practiceRows * 69) + ($practiceRows * 46) + ($practiceRows * 3 * 10); // Each size + margins
            $kanjiSectionHeight = $kanjiHeaderHeight + $practiceRowHeight + 20;
            $kanjiPerPage = max(1, floor($availableContentHeight / $kanjiSectionHeight));
        } else {
            // Single size mode - use calculated grid_size from form
            $cellSize = $cellSizes[$practiceSize];
            $gridsPerRow = $cellSize['grids_per_row'];
            $rowsNeeded = ceil($gridSize / $gridsPerRow);
            $practiceRowHeight = ($cellSize['height'] + 10) * $rowsNeeded; // cell height + margins
            
            // Total height per kanji section
            $kanjiSectionHeight = $kanjiHeaderHeight + $practiceRowHeight + 20;
            
            // Calculate how many kanji fit per page
            $kanjiPerPage = max(1, floor($availableContentHeight / $kanjiSectionHeight));
        }
    @endphp

    @foreach($kanjiData as $index => $item)
        <div class="kanji-section {{ $index > 0 && $index % $kanjiPerPage === 0 ? 'page-break' : '' }}">
            <div class="vocabulary-header">
                {{ $item['vocabulary']->word_japanese }} - {{ $item['vocabulary']->word_english }}
            </div>
<!--             
            @if(($item['vocabulary']->part_of_speech && is_array($item['vocabulary']->part_of_speech) && count($item['vocabulary']->part_of_speech) > 0) || $item['vocabulary']->jlpt_level)
                <div class="vocabulary-info">
                    @if($item['vocabulary']->part_of_speech && is_array($item['vocabulary']->part_of_speech) && count($item['vocabulary']->part_of_speech) > 0)
                        Part of Speech: {{ collect($item['vocabulary']->part_of_speech)->map(fn($pos) => ucfirst(str_replace('_', ' ', $pos)))->join(', ') }}
                    @endif
                    @if($item['vocabulary']->jlpt_level)
                        | JLPT Level: {{ $item['vocabulary']->jlpt_level }}
                    @endif
                </div>
            @endif -->

            @foreach($item['kanji'] as $kanjiInfo)
                <table style="width: 100%; margin-bottom: 15px; border-collapse: collapse;">
                    <tr>
                        {{-- Example kanji on the left --}}
                        <td style="width: 120px; vertical-align: top; padding-right: 10px;">
                            <div class="kanji-cell-container">
                                <div class="kanji-cell size-{{ $practiceSize === 'mixed' ? 'large' : $practiceSize }}">
                                    @if($kanjiInfo['svg_available'] && $item['include_stroke_order'] && $kanjiInfo['svg_content'])
                                        {{-- Show SVG stroke order --}}
                                        <div class="kanji-stroke-order size-{{ $practiceSize === 'mixed' ? 'large' : $practiceSize }}">
                                            <img src="{{ public_path('svg/'.$kanjiInfo['codepoint'].'.svg') }}" alt="Kanji Stroke Order" height="100%" width="100%">
                                        </div>
                                    @else
                                        {{-- Show character --}}
                                        @php
                                            $exampleSize = $practiceSize === 'mixed' ? 'large' : $practiceSize;
                                            $cellDimension = $cellSizes[$exampleSize]['width'];
                                        @endphp
                                        <div class="kanji-character size-{{ $exampleSize }}" style="display: table-cell; vertical-align: middle; text-align: center; height: {{ $cellDimension }}px; width: {{ $cellDimension }}px; color: #333;">
                                            {{ $kanjiInfo['character'] }}
                                        </div>
                                    @endif
                                    <div class="practice-grid"></div>
                                </div>
                            </div>
                        </td>

                        {{-- Practice grids on the right --}}
                        <td style="vertical-align: top;">
                            @if($practiceSize === 'mixed')
                                {{-- Mixed mode: full rows of large, medium, and small --}}
                                @php
                                    $practiceRows = ceil($gridSize / $mixedTotalGrids);
                                    $largePerRow = $isLandscape ? $gridsPerRowLandscape['large'] : 5;
                                    $mediumPerRow = $isLandscape ? $gridsPerRowLandscape['medium'] : 7;
                                    $smallPerRow = $isLandscape ? $gridsPerRowLandscape['small'] : 10;
                                    $mixedSizes = [
                                        ['size' => 'large', 'count' => $largePerRow, 'opacity' => 0.3],
                                        ['size' => 'medium', 'count' => $mediumPerRow, 'opacity' => 0.2],
                                        ['size' => 'small', 'count' => $smallPerRow, 'opacity' => 0.1]
                                    ];
                                @endphp
                                
                                @for($row = 0; $row < $practiceRows; $row++)
                                    @foreach($mixedSizes as $sizeInfo)
                                        <div style="margin-bottom: 8px;">
                                            <table style="border-collapse: collapse;">
                                                <tr>
                                                    @for($i = 0; $i < $sizeInfo['count']; $i++)
                                                        <td style="padding-right: 4px; padding-bottom: 4px;">
                                                            <div class="kanji-cell-container">
                                                                <div class="kanji-cell practice size-{{ $sizeInfo['size'] }}">
                                                                    @if($i === 0 && $kanjiInfo['svg_available'] && $kanjiInfo['svg_content'])
                                                                        {{-- First grid in each row: show SVG at opacity --}}
                                                                        @php
                                                                            $cellDim = $cellSizes[$sizeInfo['size']]['width'];
                                                                        @endphp
                                                                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: {{ $sizeInfo['opacity'] }}; z-index: 5;">
                                                                            <img src="{{ public_path('svg/'.$kanjiInfo['codepoint'].'.svg') }}" alt="Kanji Reference" style="width: {{ $cellDim }}px; height: {{ $cellDim }}px;">
                                                                        </div>
                                                                    @endif
                                                                    <div class="practice-grid"></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    @endfor
                                                </tr>
                                            </table>
                                        </div>
                                    @endforeach
                                @endfor
                            @else
                                {{-- Single size mode --}}
                                <table style="border-collapse: collapse;">
                                    @php
                                        $cellSize = $cellSizes[$practiceSize];
                                        $gridsPerRow = $cellSize['grids_per_row'];
                                        $totalGrids = $gridSize;
                                        $currentGrid = 0;
                                        $currentRow = 0;
                                    @endphp
                                    
                                    <tr>
                                        @for($i = 0; $i < $totalGrids; $i++)
                                            @php
                                                $gridInCurrentRow = $currentGrid % $gridsPerRow;
                                            @endphp
                                            
                                            <td style="padding-right: 4px; padding-bottom: 4px;">
                                                <div class="kanji-cell-container">
                                                    <div class="kanji-cell practice size-{{ $practiceSize }}">
                                                        @if($gridInCurrentRow === 0 && $kanjiInfo['svg_available'] && $kanjiInfo['svg_content'])
                                                            {{-- First grid in row: show SVG at opacity --}}
                                                            @php
                                                                $cellDim = $cellSize['width'];
                                                            @endphp
                                                            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.3; z-index: 5;">
                                                                <img src="{{ public_path('svg/'.$kanjiInfo['codepoint'].'.svg') }}" alt="Kanji Reference" style="width: {{ $cellDim }}px; height: {{ $cellDim }}px;">
                                                            </div>
                                                        @elseif($gridInCurrentRow === 1 && $kanjiInfo['svg_available'] && $kanjiInfo['svg_content'])
                                                            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.1; z-index: 5;">
                                                                <img src="{{ public_path('svg/'.$kanjiInfo['codepoint'].'.svg') }}" alt="Kanji Reference" style="width: {{ $cellDim }}px; height: {{ $cellDim }}px;">
                                                            </div>
                                                        @endif
                                                        <div class="practice-grid"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            @php $currentGrid++; @endphp
                                            
                                            {{-- Break to new row when we hit the limit --}}
                                            @if($gridInCurrentRow === ($gridsPerRow - 1) && $i < ($totalGrids - 1))
                                    </tr>
                                    <tr>
                                                @php 
                                                    $currentRow++; 
                                                    $currentGrid = 0;
                                                @endphp
                                            @endif
                                        @endfor
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>
                </table>
            @endforeach
        </div>
    @endforeach

    {{-- Global footer for all pages --}}
    <div class="worksheet-footer">
        {{ $worksheet->name }} | Generated {{ now()->format('M j, Y') }} | Ganbatte.io
    </div>

</body>
</html>
