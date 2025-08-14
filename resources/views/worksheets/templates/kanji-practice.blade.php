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
            padding: 15px;
            font-size: 12px;
            color: #333;
        }
        
        .japanese-text {
            font-family: 'M PLUS 2', 'courier', sans-serif !important;
            font-size: 16px !important;
        }
        
        .kanji-character {
            font-family: 'M PLUS 2', 'courier', sans-serif !important;
            font-size: 48px !important;
            line-height: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: normal;
            margin: 0 0 10px 0;
        }

        .header .subtitle {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
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
            width: 100px;
            height: 100px;
            border: 2px solid #333;
            position: relative;
            margin: 0 auto;
            background: white;
        }

        .kanji-cell.practice {
            border: 1px solid #ccc;
        }

        .kanji-stroke-order {
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .kanji-stroke-order svg {
            width: 100px !important;
            height: 100px !important;
            max-width: 100px;
            max-height: 100px;
        }

        .practice-grid {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
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

        .footer {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #999;
        }

        @page {
            margin: 15mm 15mm 15mm 15mm;
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
    <div class="header">
        <h1>{{ $worksheet->name }}</h1>
        <div class="subtitle">{{ $worksheet->lesson->title_english ?? 'Custom Worksheet' }}</div>
        <div class="subtitle">Generated on {{ now()->format('F j, Y') }}</div>
    </div>

    @php
        // Calculate optimal page breaks based on content size
        $gridSize = $settings['grid_size'] ?? 6;
        $isLandscape = ($settings['orientation'] ?? 'portrait') === 'landscape';
        
        // Estimate heights (in approximate pixels for DomPDF)
        $headerHeight = 80;
        $kanjiHeaderHeight = 45; // vocabulary header + info
        $practiceRowHeight = 110; // 100px grid + margins
        
        // Calculate rows needed for grid size
        $gridsPerRow = $isLandscape ? 7 : 5;
        $rowsNeeded = ceil($gridSize / $gridsPerRow);
        
        // Total height per kanji section
        $kanjiSectionHeight = $kanjiHeaderHeight + ($practiceRowHeight * $rowsNeeded) + 20; // +margin
        
        // Available page height (rough estimates for A4)
        $pageHeight = $isLandscape ? 520 : 750; // Landscape vs Portrait usable height
        $availableHeight = $pageHeight - $headerHeight;
        
        // How many kanji fit per page
        $kanjiPerPage = max(1, floor($availableHeight / $kanjiSectionHeight));
    @endphp

    @foreach($kanjiData as $index => $item)
        <div class="kanji-section {{ $index > 0 && $index % $kanjiPerPage === 0 ? 'page-break' : '' }}">
            <div class="vocabulary-header">
                {{ $item['vocabulary']->word_japanese }} - {{ $item['vocabulary']->word_english }}
            </div>
            
            @if($item['vocabulary']->part_of_speech || $item['vocabulary']->jlpt_level)
                <div class="vocabulary-info">
                    @if($item['vocabulary']->part_of_speech)
                        Part of Speech: {{ ucfirst($item['vocabulary']->part_of_speech) }}
                    @endif
                    @if($item['vocabulary']->jlpt_level)
                        | JLPT Level: {{ $item['vocabulary']->jlpt_level }}
                    @endif
                </div>
            @endif

            @foreach($item['kanji'] as $kanjiInfo)
                <table style="width: 100%; margin-bottom: 15px; border-collapse: collapse;">
                    <tr>
                        {{-- Example kanji on the left --}}
                        <td style="width: 120px; vertical-align: top; padding-right: 10px;">
                            <div class="kanji-cell-container">
                                <div class="kanji-cell">
                                    @if($kanjiInfo['svg_available'] && $item['include_stroke_order'] && $kanjiInfo['svg_content'])
                                        {{-- Show SVG stroke order --}}
                                        <div class="kanji-stroke-order">
                                            <img src="{{ public_path('svg/'.$kanjiInfo['codepoint'].'.svg') }}" alt="Kanji Stroke Order" style="width: 100px; height: 100px;">
                                        </div>
                                    @else
                                        {{-- Show character --}}
                                        <div class="kanji-character" style="display: table-cell; vertical-align: middle; text-align: center; height: 100px; width: 100px; color: #333; font-size: 36px; font-family: 'M PLUS 2', 'courier', sans-serif;">
                                            {{ $kanjiInfo['character'] }}
                                        </div>
                                    @endif
                                    <div class="practice-grid"></div>
                                </div>
                            </div>
                        </td>

                        {{-- Practice grids on the right --}}
                        <td style="vertical-align: top;">
                            <table style="border-collapse: collapse;">
                                @php
                                    $isLandscape = ($settings['orientation'] ?? 'portrait') === 'landscape';
                                    $firstRowMax = $isLandscape ? 7 : 5;
                                    $subsequentRowMax = $isLandscape ? 7 : 5;
                                    $totalGrids = $settings['grid_size'];
                                    $currentGrid = 0;
                                    $currentRow = 0;
                                @endphp
                                
                                <tr>
                                    @for($i = 0; $i < $totalGrids; $i++)
                                        @php
                                            $currentRowMax = ($currentRow === 0) ? $firstRowMax : $subsequentRowMax;
                                            $gridInCurrentRow = $currentGrid % $currentRowMax;
                                        @endphp
                                        
                                        <td style="padding-right: 4px; padding-bottom: 4px;">
                                            <div class="kanji-cell-container">
                                                <div class="kanji-cell practice">
                                                    @if($gridInCurrentRow === 0 && $kanjiInfo['svg_available'] && $kanjiInfo['svg_content'])
                                                        {{-- First grid in row: show SVG at half opacity --}}
                                                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.3; z-index: 1;">
                                                            <img src="{{ public_path('svg/'.$kanjiInfo['codepoint'].'.svg') }}" alt="Kanji Reference" style="width: 100px; height: 100px;">
                                                        </div>
                                                    @elseif($gridInCurrentRow === 1 && $kanjiInfo['svg_available'] && $kanjiInfo['svg_content'])
                                                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.1; z-index: 1;">
                                                            <img src="{{ public_path('svg/'.$kanjiInfo['codepoint'].'.svg') }}" alt="Kanji Reference" style="width: 100px; height: 100px;">
                                                        </div>
                                                    @endif
                                                    <div class="practice-grid">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        @php $currentGrid++; @endphp
                                        
                                        {{-- Break to new row when we hit the limit for current row --}}
                                        @if($gridInCurrentRow === ($currentRowMax - 1) && $i < ($totalGrids - 1))
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
                        </td>
                    </tr>
                </table>
            @endforeach
        </div>
    @endforeach

    <div class="footer">
        Generated by Ganbatte - Japanese Learning Platform | KanjiVG Project
    </div>
</body>
</html>
