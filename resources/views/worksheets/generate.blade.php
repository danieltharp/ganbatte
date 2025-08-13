@extends('layouts.app')

@section('title', 'Generate Kanji Worksheet - ' . $worksheet->name)

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Generate Kanji Worksheet</h1>
    <a href="{{ route('worksheets.show', $worksheet) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Worksheet
    </a>
</div>

<div class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-6">{{ $worksheet->name }}</div>

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Generation Form -->
                        <div class="lg:col-span-2">
                            <h3 class="text-lg font-semibold mb-4">Worksheet Settings</h3>
                            
                            <form method="POST" action="{{ route('worksheets.kanji-pdf', $worksheet) }}" class="space-y-6">
                                @csrf
                                
                                <!-- Paper Settings -->
                                <div class="bg-gray-50 p-4 rounded-lg dark:bg-gray-700">
                                    <h4 class="font-medium text-gray-900 mb-3 dark:text-gray-100">Paper Settings</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="paper_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Paper Size</label>
                                            <select name="paper_size" id="paper_size" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                                                <option value="A4" selected>A4 (210 × 297 mm)</option>
                                                <option value="Letter">US Letter (8.5 × 11 in)</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label for="orientation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Orientation</label>
                                            <select name="orientation" id="orientation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                                                <option value="portrait" selected>Portrait</option>
                                                <option value="landscape">Landscape</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Layout Settings -->
                                <div class="bg-gray-50 p-4 rounded-lg dark:bg-gray-700">
                                    <h4 class="font-medium text-gray-900 mb-3 dark:text-gray-100">Layout Settings</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="grid_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Practice Cells per Kanji</label>
                                            <select name="grid_size" id="grid_size" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                                                <option value="5">5 cells</option>
                                                <option value="10" selected>10 cells</option>
                                                <option value="15">15 cells</option>
                                                <option value="20">20 cells</option>
                                            </select>
                                            <p class="mt-1 text-sm text-gray-500">Includes 1 example + practice cells</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Content Settings -->
                                <div class="bg-gray-50 p-4 rounded-lg dark:bg-gray-700">
                                    <h4 class="font-medium text-gray-900 mb-3 dark:text-gray-100">Content Options</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="include_stroke_order" id="include_stroke_order" value="1" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <label for="include_stroke_order" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Include stroke order guides</label>
                                        </div>
                                        
                                        <div class="flex items-center">
                                            <input type="checkbox" name="include_readings" id="include_readings" value="1" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <label for="include_readings" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Include character readings</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vocabulary Selection -->
                                <div class="bg-gray-50 p-4 rounded-lg dark:bg-gray-700">
                                    <h4 class="font-medium text-gray-900 mb-3 dark:text-gray-100">Vocabulary Selection</h4>
                                    <p class="text-sm text-gray-600 mb-3 dark:text-gray-400">Select which vocabulary items to include in the worksheet:</p>
                                    
                                    <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-md bg-white dark:bg-gray-700">
                                        @if($vocabulary->count() > 0)
                                            <div class="p-3">
                                                <label class="flex items-center mb-2">
                                                    <input type="checkbox" id="select_all" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Select All</span>
                                                </label>
                                            </div>
                                            <div class="border-t border-gray-200"></div>
                                            @foreach($vocabulary as $vocab)
                                                @php
                                                    $kanjiData = \App\Helpers\KanjiSvgHelper::getKanjiDataForVocabulary($vocab);
                                                    $hasAvailableKanji = collect($kanjiData)->some(function($kanji) {
                                                        return $kanji['svg_available'];
                                                    });
                                                @endphp
                                                <div class="p-3 border-b border-gray-100 {{ !$hasAvailableKanji ? 'bg-gray-50 dark:bg-gray-700' : '' }}">
                                                    <label class="flex items-center">
                                                        <input type="checkbox" name="vocabulary_ids[]" value="{{ $vocab->id }}" 
                                                               class="vocabulary-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                               {{ $hasAvailableKanji ? 'checked' : 'disabled' }}>
                                                        <div class="ml-3 flex-1">
                                                            <div class="flex items-center justify-between">
                                                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $vocab->word_japanese }} - {{ $vocab->word_english }}
                                                                </span>
                                                                @if(!$hasAvailableKanji)
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                        No SVG
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            @if($hasAvailableKanji)
                                                                <div class="text-xs text-gray-500 mt-1 dark:text-gray-400">
                                                                    Kanji: 
                                                                    @foreach($kanjiData as $kanji)
                                                                        @if($kanji['svg_available'])
                                                                            <span class="inline-block mr-1 px-1 bg-green-100 text-green-800 rounded">{{ $kanji['character'] }}</span>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="p-4 text-center text-gray-500">
                                                No vocabulary items marked for kanji worksheets found.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Generate Button -->
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Preview & Print Worksheet
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Statistics Sidebar -->
                        <div class="lg:col-span-1">
                            <h3 class="text-lg font-semibold mb-4 dark:text-gray-100">Kanji Statistics</h3>
                            
                            <div class="bg-blue-50 p-4 rounded-lg mb-4">
                                <h4 class="font-medium text-blue-900 mb-2 dark:text-blue-100">Coverage Overview</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-blue-700">Total Kanji:</span>
                                        <span class="text-sm font-medium text-blue-900">{{ $kanjiStats['total_kanji'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-blue-700">Available SVGs:</span>
                                        <span class="text-sm font-medium text-blue-900">{{ $kanjiStats['available_kanji'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-blue-700">Coverage:</span>
                                        <span class="text-sm font-medium text-blue-900">{{ $kanjiStats['coverage_percentage'] }}%</span>
                                    </div>
                                </div>
                                
                                @if($kanjiStats['coverage_percentage'] > 0)
                                    <div class="mt-3">
                                        <div class="bg-blue-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $kanjiStats['coverage_percentage'] }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if(!empty($kanjiStats['missing_kanji']))
                                <div class="bg-yellow-50 p-4 rounded-lg mb-4">
                                    <h4 class="font-medium text-yellow-900 mb-2 dark:text-yellow-100">Missing SVGs</h4>
                                    <p class="text-sm text-yellow-700 mb-2">These kanji don't have SVG files available:</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($kanjiStats['missing_kanji'], 0, 10) as $kanji)
                                            <span class="inline-block px-2 py-1 bg-yellow-200 text-yellow-800 text-xs rounded">{{ $kanji }}</span>
                                        @endforeach
                                        @if(count($kanjiStats['missing_kanji']) > 10)
                                            <span class="text-xs text-yellow-600">+{{ count($kanjiStats['missing_kanji']) - 10 }} more</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="font-medium text-green-900 mb-2 dark:text-green-100">About KanjiVG</h4>
                                <p class="text-sm text-green-700">
                                    This worksheet uses stroke order data from the KanjiVG project, providing authentic Japanese writing guidance.
                                </p>
                            </div>
                        </div>
                    </div>
    </div>
</div>

<script>
        // Select all functionality
        document.getElementById('select_all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.vocabulary-checkbox:not(:disabled)');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Update select all when individual checkboxes change
        document.querySelectorAll('.vocabulary-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allCheckboxes = document.querySelectorAll('.vocabulary-checkbox:not(:disabled)');
                const checkedCheckboxes = document.querySelectorAll('.vocabulary-checkbox:not(:disabled):checked');
                const selectAllCheckbox = document.getElementById('select_all');
                
                selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
            });
        });
</script>
@endsection
