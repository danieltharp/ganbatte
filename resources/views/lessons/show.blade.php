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
                                                <div class="text-sm flex items-center justify-between">
                                                    <div class="flex items-center space-x-2">
                                                        @if($contentItem->type === 'section')
                                                            @php
                                                                $progress = $sectionProgress->get($contentItem->content->id);
                                                                $isCompleted = $progress && $progress->isCompleted();
                                                            @endphp
                                                            
                                                            @if($isCompleted)
                                                                <span class="text-green-500" title="Completed {{ $progress->completed_at->format('M j') }}">✅</span>
                                                            @else
                                                                <span class="text-blue-600 dark:text-blue-400">📄</span>
                                                            @endif
                                                            
                                                            <a href="{{ route('sections.show', $contentItem->content->id) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 {{ $isCompleted ? 'line-through opacity-75' : '' }}">
                                                                {{ $contentItem->content->name }}
                                                            </a>
                                                            
                                                            @if($isCompleted && $progress->attempts > 1)
                                                                <span class="text-xs text-gray-500">({{ $progress->attempts }}× attempts)</span>
                                                            @endif
                                                            
                                                        @elseif($contentItem->type === 'exercise')
                                                            @php
                                                                $exerciseAttempt = $exerciseProgress->get($contentItem->content->id);
                                                                $exerciseCompleted = $exerciseAttempt !== null;
                                                            @endphp
                                                            
                                                            @if($exerciseCompleted)
                                                                <span class="text-green-500" title="Completed {{ $exerciseAttempt->completed_at->format('M j') }} - Score: {{ $exerciseAttempt->percentage }}%">✅</span>
                                                            @else
                                                                <span class="text-green-600 dark:text-green-400">✏️</span>
                                                            @endif
                                                            
                                                            <a href="{{ route('exercises.show', $contentItem->content->id) }}" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 {{ $exerciseCompleted ? 'line-through opacity-75' : '' }}">
                                                                {{ $contentItem->content->name ?? $contentItem->content->title ?? 'Exercise' }}
                                                            </a>
                                                            
                                                            @if($exerciseCompleted)
                                                                <a href="{{ route('exercises.results', $exerciseAttempt->id) }}" class="text-xs text-gray-500 hover:text-blue-600">({{ $exerciseAttempt->percentage }}%)</a>
                                                                @if($exerciseAttempt->hasManualCorrections())
                                                                    <span class="text-xs text-blue-500" title="Includes manual corrections">✎</span>
                                                                @endif
                                                            @endif
                                                            
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

        <!-- Articles Section -->
        @if($lesson->articles->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                            <span class="mr-2">📖</span>
                            Supplementary Articles ({{ $lesson->articles->count() }})
                        </h2>
                    </div>
                    
                    <div class="space-y-4">
                        @foreach($lesson->articles as $article)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                            <a href="{{ route('articles.show', $article->id) }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ $article->title }}</a>
                                        </h3>
                                        @if($article->subtitle)
                                            <p class="text-gray-600 dark:text-gray-400 mb-3">{{ $article->subtitle }}</p>
                                        @endif
                                        
                                        @if($article->covered_vocabulary_ids && count($article->covered_vocabulary_ids) > 0)
                                            <div class="flex items-center space-x-2 mb-2">
                                                <span class="text-sm text-gray-500 dark:text-gray-400">Covers:</span>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($article->coveredVocabulary() as $vocab)
                                                        <a href="{{ route('vocabulary.show', $vocab->id) }}">
                                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                                {{ $vocab->word_japanese }}
                                                            </span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center space-x-2 ml-4">
                                        <a href="{{ route('articles.show', $article->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded-md transition-colors">
                                            Read Article →
                                        </a>
                                    </div>
                                </div>
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
                            <a href="{{ route('vocabulary.show', $vocab->id) }}">
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="japanese-text text-lg font-semibold mb-1 text-gray-900 dark:text-gray-100">
                                                @if($vocab->word_furigana)
                                                    <x-furigana-text>{{ $vocab->furigana_word }}</x-furigana-text>
                                                @else
                                                    {{ $vocab->japanese_word }}
                                                @endif
                                            </div>
                                            <div class="text-gray-900 dark:text-gray-100 font-medium">{{ $vocab->word_english }}</div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                @if($vocab->part_of_speech && is_array($vocab->part_of_speech))
                                                    {{ collect($vocab->part_of_speech)->map(fn($pos) => ucfirst(str_replace('_', ' ', $pos)))->join(', ') }}
                                                @endif
                                            </div>
                                        </div>
                                        @if($vocab->include_in_kanji_worksheet)
                                            <span class="text-xs bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 px-2 py-1 rounded">
                                                Kanji Practice
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
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
                            <a href="{{ route('grammar.show', $grammar->id) }}">
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                        @if($grammar->name_japanese)
                                            <span class="japanese-text">{{ $grammar->name_japanese }}</span> - 
                                        @endif
                                        {{ $grammar->name_english }}
                                    </h3>
                                    <div class="japanese-text text-lg mb-2 font-mono bg-gray-50 dark:bg-gray-700 p-2 rounded text-gray-900 dark:text-gray-100">
                                        {{ $grammar->pattern }}
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $grammar->usage }}</p>
                                </div>
                            </a>
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
                        📚 Study Vocabulary
                    </a>
                    <a href="{{ route('vocabulary.quiz.index', ['lesson_from' => $lesson->id, 'lesson_to' => $lesson->id]) }}" class="block w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                        🎓 Vocabulary Quiz
                    </a>
                    @if($lesson->vocabulary->where('include_in_kanji_worksheet', true)->count() > 0)
                        <a href="{{ route('worksheets.index', ['lesson_id' => $lesson->id, 'type' => 'kanji_practice']) }}" class="block w-full bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center">
                            🈂️ Kanji Practice
                        </a>
                    @endif
                    <!-- @if($lesson->questions->count() > 0)
                        <a href="{{ route('questions.index', ['lesson_id' => $lesson->id]) }}" class="block w-full bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded text-center">
                            ❓ Practice Questions
                        </a>
                    @endif -->
                    @if($lesson->worksheets->count() > 0)
                        <a href="{{ route('worksheets.index', ['lesson_id' => $lesson->id]) }}" class="block w-full bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-center">
                            📋 Worksheets
                        </a>
                    @endif
                    
                    @auth
                        @php
                            // Count unique items only (sections + exercises can span multiple pages)
                            $uniqueTotalItems = $lesson->pages
                                ->flatMap(fn($page) => $page->content)
                                ->whereIn('type', ['section', 'exercise'])
                                ->pluck('id')
                                ->unique()
                                ->count();
                            $completedItems = $sectionProgress->where('completed_at', '!=', null)->count() + $exerciseProgress->count();
                            $overallProgress = $uniqueTotalItems > 0 ? round(($completedItems / $uniqueTotalItems) * 100) : 0;
                        @endphp
                        
                        @if($uniqueTotalItems > 0)
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-3 mt-4">
                                <div class="text-center">
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Lesson Progress</div>
                                    <div class="text-2xl font-bold mb-1 {{ $overallProgress === 100 ? 'text-green-500' : 'text-indigo-500' }}">
                                        {{ $overallProgress }}%
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all duration-500 {{ $overallProgress === 100 ? 'bg-green-500' : 'bg-indigo-500' }}" style="width: {{ $overallProgress }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
        </div>

        @auth
            <!-- Progress Summary -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">📊</span>
                        Your Progress
                    </h3>
                    
                    @php
                        // Count unique sections and exercises (not page references)
                        $uniqueSectionIds = $lesson->pages
                            ->flatMap(fn($page) => $page->content)
                            ->where('type', 'section')
                            ->pluck('id')
                            ->unique();
                        $totalSections = $uniqueSectionIds->count();
                        
                        $uniqueExerciseIds = $lesson->pages
                            ->flatMap(fn($page) => $page->content)
                            ->where('type', 'exercise')
                            ->pluck('id')
                            ->unique();
                        $totalExercises = $uniqueExerciseIds->count();
                        
                        $completedSections = $sectionProgress->where('completed_at', '!=', null)->count();
                        $completedExercises = $exerciseProgress->count();
                        
                        $sectionPercentage = $totalSections > 0 ? round(($completedSections / $totalSections) * 100) : 0;
                        $exercisePercentage = $totalExercises > 0 ? round(($completedExercises / $totalExercises) * 100) : 0;
                    @endphp
                    
                    <div class="space-y-4">
                        <!-- Section Progress -->
                        @if($totalSections > 0)
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Sections</span>
                                    <span class="text-sm font-semibold dark:text-white">{{ $completedSections }}/{{ $totalSections }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: {{ $sectionPercentage }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $sectionPercentage }}% completed</div>
                            </div>
                        @endif
                        
                        <!-- Exercise Progress -->
                        @if($totalExercises > 0)
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Exercises</span>
                                    <span class="text-sm font-semibold dark:text-white">{{ $completedExercises }}/{{ $totalExercises }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: {{ $exercisePercentage }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $exercisePercentage }}% completed</div>
                            </div>
                        @endif
                        
                        <!-- Overall Progress -->
                        @php
                            $uniqueTotalItems = $uniqueSectionIds->count() + $uniqueExerciseIds->count();
                            $overallPercentage = $uniqueTotalItems > 0 ? round(($completedItems / $uniqueTotalItems) * 100) : 0;
                        @endphp
                        
                        @if($uniqueTotalItems > 0)
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Overall</span>
                                    <span class="text-sm font-bold dark:text-white">{{ $completedItems }}/{{ $uniqueTotalItems }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-indigo-500 h-3 rounded-full transition-all duration-300" style="width: {{ $overallPercentage }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $overallPercentage }}% lesson completion</div>
                            </div>
                        @endif
                        
                        @if($completedItems === $uniqueTotalItems && $uniqueTotalItems > 0)
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3 text-center">
                                <span class="text-green-600 dark:text-green-400 font-semibold">🎉 Lesson Complete!</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endauth

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