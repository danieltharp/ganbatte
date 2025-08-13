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
            padding: 20px;
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
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 24px;
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
            margin-bottom: 50px;
            page-break-inside: avoid;
        }

        .vocabulary-header {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .vocabulary-info {
            font-size: 12px;
            color: #666;
            margin-bottom: 15px;
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
            margin: {{ $settings['margins']['top'] ?? 20 }}mm {{ $settings['margins']['right'] ?? 20 }}mm {{ $settings['margins']['bottom'] ?? 20 }}mm {{ $settings['margins']['left'] ?? 20 }}mm;
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

    @foreach($kanjiData as $index => $item)
        <div class="kanji-section {{ $index > 0 && $index % 3 === 0 ? 'page-break' : '' }}">
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

            <div class="kanji-grid">
                @foreach($item['kanji'] as $kanjiInfo)
                    @if($kanjiInfo['svg_available'])
                        {{-- First cell: Example with stroke order --}}
                        <div class="kanji-cell-container">
                            <div class="kanji-cell">
                                @if($item['include_stroke_order'] && $kanjiInfo['svg_content'])
                                    @php
                                        $optimizedSvg = \App\Helpers\KanjiSvgHelper::optimizeSvgForPdf($kanjiInfo['svg_content']);
                                    @endphp

                                    @if(!empty($optimizedSvg))
                                        {{-- Try SVG first --}}
                                        <div class="kanji-stroke-order">
                                            <img src="{{ public_path('svg/'.$kanjiInfo['codepoint'].'.svg') }}" alt="Kanji Stroke Order" style="width: 100px; height: 100px;">
                                        </div>
                                    @endif
                                @else
                                    {{-- No stroke order requested or no SVG --}}
                                    <div class="kanji-character" style="display: flex; align-items: center; justify-content: center; height: 100%; color: #333; font-size: 36px; font-family: 'M PLUS 2', 'courier', sans-serif;">
                                        {{ $kanjiInfo['character'] }}
                                    </div>
                                @endif
                                <div class="practice-grid"></div>
                            </div>
                            @if($item['include_readings'])
                                <div class="kanji-reading" style="font-family: 'M PLUS 2', 'courier', sans-serif;">{{ $kanjiInfo['character'] }}</div>
                            @endif
                        </div>

                        {{-- Additional practice cells (empty) --}}
                        @for($i = 0; $i < ($settings['grid_size'] - 1); $i++)
                            <div class="kanji-cell-container">
                                <div class="kanji-cell practice">
                                    <div class="practice-grid"></div>
                                </div>
                            </div>
                        @endfor
                    @else
                        {{-- Fallback for missing SVG --}}
                        <div class="kanji-cell-container">
                            <div class="kanji-cell">
                                <div class="kanji-character" style="display: flex; align-items: center; justify-content: center; height: 100%; color: #333;">
                                    {!! \App\Helpers\KanjiSvgHelper::encodeJapaneseForPdf($kanjiInfo['character']) !!}
                                </div>
                                <div class="practice-grid"></div>
                            </div>
                            @if($item['include_readings'])
                                <div class="kanji-reading" style="font-family: 'M PLUS 2', 'courier', sans-serif;">{{ $kanjiInfo['character'] }}</div>
                            @endif
                        </div>

                        {{-- Practice cells --}}
                        @for($i = 0; $i < ($settings['grid_size'] - 1); $i++)
                            <div class="kanji-cell-container">
                                <div class="kanji-cell practice">
                                    <div class="practice-grid"></div>
                                </div>
                            </div>
                        @endfor
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="footer">
        Generated by Ganbatte - Japanese Learning Platform | KanjiVG Project
    </div>
</body>
</html>
