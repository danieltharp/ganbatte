@extends('layouts.app')

@section('title', 'Grammar Points')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Grammar Points</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                Explore Japanese grammar patterns and structures
            </p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('grammar.index') }}" class="md:space-y-0 md:flex md:items-end md:space-x-4">
            <!-- Search -->
            <div class="flex-1 px-2">
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Search Grammar Points
                </label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search by name, pattern, or usage..."
                       class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Lesson Filter -->
            <div class="px-2">
                <label for="lesson_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Lesson
                </label>
                <select id="lesson_id" 
                        name="lesson_id"
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Lessons</option>
                    @foreach($lessons as $lesson)
                        <option value="{{ $lesson->id }}" {{ request('lesson_id') == $lesson->id ? 'selected' : '' }}>
                            Lesson {{ $lesson->chapter }}: {{ $lesson->title_english }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- JLPT Level Filter -->
            <div class="px-2">
                <label for="jlpt_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    JLPT Level
                </label>
                <select id="jlpt_level" 
                        name="jlpt_level"
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Levels</option>
                    @foreach($jlptLevels as $level)
                        <option value="{{ $level }}" {{ request('jlpt_level') == $level ? 'selected' : '' }}>
                            {{ $level }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex space-x-2">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Filter
                </button>
                <a href="{{ route('grammar.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded content-center">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Showing {{ $grammarPoints->firstItem() ?? 0 }}-{{ $grammarPoints->lastItem() ?? 0 }} of {{ $grammarPoints->total() }} grammar points
        </p>
    </div>
</div>

<!-- Grammar Points Grid -->
@if($grammarPoints->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        @foreach($grammarPoints as $grammar)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                @if($grammar->name_japanese)
                                    <div class="japanese-text text-lg mb-1">
                                        @if($grammar->name_furigana)
                                            <x-furigana-text>{{ $grammar->name_furigana }}</x-furigana-text>
                                        @else
                                            {{ $grammar->name_japanese }}
                                        @endif
                                    </div>
                                @endif
                                <div class="text-base">{{ $grammar->name_english }}</div>
                            </h3>
                        </div>
                        @if($grammar->jlpt_level)
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium ml-2
                                @if($grammar->jlpt_level === 'N5') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @elseif($grammar->jlpt_level === 'N4') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                @elseif($grammar->jlpt_level === 'N3') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @elseif($grammar->jlpt_level === 'N2') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                {{ $grammar->jlpt_level }}
                            </span>
                        @endif
                    </div>

                    <!-- Pattern -->
                    <div class="japanese-text text-lg mb-3 font-mono bg-gray-50 dark:bg-gray-700 p-3 rounded border-l-4 border-blue-500">
                        {{ $grammar->pattern }}
                    </div>

                    <!-- Usage -->
                    @if($grammar->usage)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            {{ Str::limit($grammar->usage, 100) }}
                        </p>
                    @endif

                    <!-- Lesson Info -->
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-500 mb-4">
                        <span>
                            @if($grammar->lesson)
                                Lesson {{ $grammar->lesson->chapter }}: {{ $grammar->lesson->title_english }}
                            @else
                                No lesson assigned
                            @endif
                        </span>
                        @if($grammar->examples && count($grammar->examples) > 0)
                            <span>{{ count($grammar->examples) }} example{{ count($grammar->examples) > 1 ? 's' : '' }}</span>
                        @endif
                    </div>

                    <!-- Action Button -->
                    <a href="{{ route('grammar.show', $grammar->id) }}" 
                       class="block w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center transition-colors">
                        View Details
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="flex justify-center">
        {{ $grammarPoints->appends(request()->query())->links() }}
    </div>
@else
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8 text-center">
        <div class="text-gray-400 dark:text-gray-600 mb-4">
            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Grammar Points Found</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
            @if(request()->hasAny(['search', 'lesson_id', 'jlpt_level']))
                Try adjusting your filters or search terms.
            @else
                Grammar points will appear here once lesson content is loaded.
            @endif
        </p>
        @if(request()->hasAny(['search', 'lesson_id', 'jlpt_level']))
            <a href="{{ route('grammar.index') }}" class="text-blue-500 hover:text-blue-700">
                Clear all filters →
            </a>
        @endif
    </div>
@endif
@endsection 