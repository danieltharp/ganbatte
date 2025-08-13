@extends('layouts.app')

@section('title', $worksheet->name)

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $worksheet->name }}</h1>
    <a href="{{ route('worksheets.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Worksheets
    </a>
</div>

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Worksheet Details -->
                        <div class="lg:col-span-2">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold mb-2">Worksheet Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Name</label>
                                        <p class="text-sm text-gray-900">{{ $worksheet->name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Type</label>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $worksheet->type === 'kanji_practice' ? 'bg-purple-100 text-purple-800' : 
                                               ($worksheet->type === 'hiragana_practice' ? 'bg-blue-100 text-blue-800' : 
                                               ($worksheet->type === 'katakana_practice' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                            {{ str_replace('_', ' ', ucfirst($worksheet->type)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Lesson</label>
                                        <p class="text-sm text-gray-900">
                                            @if($worksheet->lesson)
                                                <a href="{{ route('lessons.show', $worksheet->lesson) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ $worksheet->lesson->title_english }} (Lesson {{ $worksheet->lesson->chapter }})
                                                </a>
                                            @else
                                                Custom Worksheet
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Template</label>
                                        <p class="text-sm text-gray-900">{{ $worksheet->template ?? 'Default' }}</p>
                                    </div>
                                </div>
                            </div>

                            @if($worksheet->print_settings)
                                <div class="mb-6">
                                    <h3 class="text-lg font-semibold mb-2">Print Settings</h3>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Paper Size</label>
                                                <p class="text-sm text-gray-900">{{ $worksheet->print_settings['paper_size'] ?? 'A4' }}</p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Orientation</label>
                                                <p class="text-sm text-gray-900">{{ ucfirst($worksheet->print_settings['orientation'] ?? 'portrait') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Actions -->
                            <div class="flex space-x-4">
                                @if($worksheet->type === 'kanji_practice')
                                    <a href="{{ route('worksheets.generate', $worksheet) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Preview & Print
                                    </a>
                                @endif
                                
                                <a href="{{ route('worksheets.edit', $worksheet) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Edit Worksheet
                                </a>
                            </div>
                        </div>

                        <!-- Statistics Sidebar -->
                        <div class="lg:col-span-1">
                            @if($worksheet->type === 'kanji_practice' && $kanjiStats)
                                <div class="bg-blue-50 p-4 rounded-lg mb-4">
                                    <h4 class="font-medium text-blue-900 mb-3">Kanji Coverage</h4>
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
                            @endif

                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-3">Content Summary</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-700">Vocabulary Items:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $worksheet->vocabulary->count() }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-700">Grammar Points:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $worksheet->grammarPoints->count() }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-700">Questions:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $worksheet->questions->count() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    </div>
</div>
@endsection
