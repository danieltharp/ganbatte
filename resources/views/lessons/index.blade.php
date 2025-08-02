@extends('layouts.app')

@section('title', 'Lessons')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Lessons</h1>
    <a href="{{ route('lessons.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Add New Lesson
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($lessons as $lesson)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-3">
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

                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    @if($lesson->title_japanese)
                        <div class="japanese-text mb-1">
                            <x-furigana-text>{{ $lesson->furigana_title }}</x-furigana-text>
                        </div>
                    @endif
                    {{ $lesson->title_english }}
                </h3>

                @if($lesson->description)
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">
                        {{ $lesson->description }}
                    </p>
                @endif

                <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 mb-4">
                    @if($lesson->estimated_time_minutes)
                        <span>⏱️ {{ $lesson->estimated_time_minutes }} min</span>
                    @endif
                    <span>📚 {{ $lesson->vocabulary->count() }} words</span>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('lessons.show', $lesson) }}" class="flex-1 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center text-sm">
                        Study
                    </a>
                    <a href="{{ route('lessons.edit', $lesson) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-3 rounded text-sm">
                        Edit
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full">
            <div class="text-center py-12">
                <div class="text-6xl mb-4">📚</div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No lessons yet</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Get started by creating your first lesson.</p>
                <a href="{{ route('lessons.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create First Lesson
                </a>
            </div>
        </div>
    @endforelse
</div>
@endsection 