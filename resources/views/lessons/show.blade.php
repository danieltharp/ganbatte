@extends('layouts.app')

@section('title', $lesson->title_english)

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="flex items-center space-x-4 mb-2">
                <a href="{{ route('lessons.index') }}" class="text-blue-500 hover:text-blue-700">← Back to Lessons</a>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    Chapter {{ $lesson->chapter }}
                </span>
                @if($lesson->difficulty)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        @if($lesson->difficulty === 'beginner') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($lesson->difficulty === 'elementary') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @elseif($lesson->difficulty === 'intermediate') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                        {{ ucfirst($lesson->difficulty) }}
                    </span>
                @endif
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                @if($lesson->title_japanese)
                    <div class="japanese-text text-4xl mb-2">
                        <x-furigana-text>{{ $lesson->furigana_title }}</x-furigana-text>
                    </div>
                @endif
                {{ $lesson->title_english }}
            </h1>
            
            @if($lesson->description)
                <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $lesson->description }}</p>
            @endif
        </div>
    </div>

    @if($lesson->estimated_time_minutes)
        <div class="text-sm text-gray-600 dark:text-gray-400">
            ⏱️ Estimated time: {{ $lesson->estimated_time_minutes }} minutes
        </div>
    @endif
</div>

<!-- Lesson Content Tabs -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-3 space-y-6">
        <!-- Section Pages -->
        @if($lesson->pages->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Section Pages ({{ $lesson->pages->count() }})</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($lesson->pages as $page)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                            {{ $page->display_name }}
                                        </h3>
                                        @if($page->title && $page->title !== $page->display_name)
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $page->title }}</p>
                                        @endif
                                        @if($page->description)
                                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $page->description }}</p>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium ml-3
                                        @if($page->isFromTextbook()) bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                        @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                        {{ ucfirst($page->book_reference) }}
                                    </span>
                                </div>
                                
                                @if($page->content && $page->content->count() > 0)
                                    <div class="space-y-2">
                                        @foreach($page->content->take(3) as $contentItem)
                                            @if($contentItem->content)
                                                <div class="text-sm">
                                                    @if($contentItem->type === 'section')
                                                        <span class="text-blue-600 dark:text-blue-400">📄</span>
                                                        <a href="{{ route('sections.show', $contentItem->content->id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                                            {{ $contentItem->content->name }}
                                                        </a>
                                                    @elseif($contentItem->type === 'exercise')
                                                        <span class="text-green-600 dark:text-green-400">✏️</span>
                                                        <a href="{{ route('exercises.show', $contentItem->content->id) }}" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200">
                                                            {{ $contentItem->content->name ?? $contentItem->content->title ?? 'Exercise' }}
                                                        </a>
                                                    @elseif($contentItem->type === 'worksheet')
                                                        <span class="text-purple-600 dark:text-purple-400">📋</span>
                                                        @if($contentItem->content->id)
                                                            <a href="{{ route('worksheets.show', $contentItem->content->id) }}" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-200">
                                                                {{ $contentItem->content->title }}
                                                            </a>
                                                        @else
                                                            <span class="text-gray-700 dark:text-gray-300">{{ $contentItem->content->title }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                        
                                        @if($page->content->count() > 3)
                                            <div class="text-xs text-gray-500 dark:text-gray-500">
                                                ... and {{ $page->content->count() - 3 }} more {{ $page->content->count() - 3 > 1 ? 'items' : 'item' }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                
                                @if($page->learning_objectives && count($page->learning_objectives) > 0)
                                    <div class="mt-3 text-xs text-gray-600 dark:text-gray-400 border-t border-gray-200 dark:border-gray-600 pt-2">
                                        <strong>Objectives:</strong> {{ implode(', ', array_slice($page->learning_objectives, 0, 2)) }}
                                        @if(count($page->learning_objectives) > 2)
                                            <span class="text-gray-500">... +{{ count($page->learning_objectives) - 2 }} more</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Vocabulary Section -->
        @if($lesson->vocabulary->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Vocabulary ({{ $lesson->vocabulary->count() }})</h2>
                        <a href="{{ route('vocabulary.index', ['lesson_id' => $lesson->id]) }}" class="text-blue-500 hover:text-blue-700 text-sm">
                            View All →
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($lesson->vocabulary->take(8) as $vocab)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="japanese-text text-lg font-semibold mb-1">
                                            @if($vocab->word_furigana)
                                                <x-furigana-text>{{ $vocab->furigana_word }}</x-furigana-text>
                                            @else
                                                {{ $vocab->japanese_word }}
                                            @endif
                                        </div>
                                        <div class="text-gray-900 dark:text-gray-100 font-medium">{{ $vocab->word_english }}</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ $vocab->part_of_speech }}</div>
                                    </div>
                                    @if($vocab->include_in_kanji_worksheet)
                                        <span class="text-xs bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 px-2 py-1 rounded">
                                            Kanji Practice
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if($lesson->vocabulary->count() > 8)
                        <div class="mt-4 text-center">
                            <a href="{{ route('vocabulary.index', ['lesson_id' => $lesson->id]) }}" class="text-blue-500 hover:text-blue-700">
                                View all {{ $lesson->vocabulary->count() }} vocabulary items →
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Grammar Section -->
        @if($lesson->grammarPoints->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Grammar Points ({{ $lesson->grammarPoints->count() }})</h2>
                        <a href="{{ route('grammar.index', ['lesson_id' => $lesson->id]) }}" class="text-blue-500 hover:text-blue-700 text-sm">
                            View All →
                        </a>
                    </div>
                    
                    <div class="space-y-4">
                        @foreach($lesson->grammarPoints->take(3) as $grammar)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                    @if($grammar->name_japanese)
                                        <span class="japanese-text">{{ $grammar->name_japanese }}</span> - 
                                    @endif
                                    {{ $grammar->name_english }}
                                </h3>
                                <div class="japanese-text text-lg mb-2 font-mono bg-gray-50 dark:bg-gray-700 p-2 rounded">
                                    {{ $grammar->pattern }}
                                </div>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $grammar->usage }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Questions Section -->
        @if($lesson->questions->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Practice Questions ({{ $lesson->questions->count() }})</h2>
                        <a href="{{ route('questions.index', ['lesson_id' => $lesson->id]) }}" class="text-blue-500 hover:text-blue-700 text-sm">
                            Practice →
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @php
                            $questionTypes = $lesson->questions->groupBy('type');
                        @endphp
                        @foreach($questionTypes as $type => $questions)
                            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="text-2xl mb-2">
                                    @switch($type)
                                        @case('multiple_choice') 🔘 @break
                                        @case('translation_j_to_e') 🔤 @break
                                        @case('translation_e_to_j') 🈂️ @break
                                        @case('fill_blank') ✏️ @break
                                        @default 📝
                                    @endswitch
                                </div>
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $questions->count() }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">{{ str_replace('_', ' ', $type) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('vocabulary.index', ['lesson_id' => $lesson->id]) }}" class="block w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                        Study Vocabulary
                    </a>
                    @if($lesson->vocabulary->where('include_in_kanji_worksheet', true)->count() > 0)
                        <a href="{{ route('vocabulary.kanji-worksheet', ['lesson_id' => $lesson->id]) }}" class="block w-full bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center">
                            Kanji Practice
                        </a>
                    @endif
                    @if($lesson->questions->count() > 0)
                        <a href="{{ route('questions.index', ['lesson_id' => $lesson->id]) }}" class="block w-full bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded text-center">
                            Practice Questions
                        </a>
                    @endif
                    @if($lesson->worksheets->count() > 0)
                        <a href="{{ route('worksheets.index', ['lesson_id' => $lesson->id]) }}" class="block w-full bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-center">
                            Worksheets
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Lesson Stats -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Lesson Stats</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Vocabulary</span>
                        <span class="font-semibold dark:text-white">{{ $lesson->vocabulary->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Kanji Items</span>
                        <span class="font-semibold dark:text-white">{{ $lesson->vocabulary->where('include_in_kanji_worksheet', true)->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Grammar Points</span>
                        <span class="font-semibold dark:text-white">{{ $lesson->grammarPoints->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Questions</span>
                        <span class="font-semibold dark:text-white">{{ $lesson->questions->count() }}</span>
                    </div>
                    @if($lesson->pages->count() > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Pages</span>
                            <span class="font-semibold dark:text-white">{{ $lesson->pages->count() }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 