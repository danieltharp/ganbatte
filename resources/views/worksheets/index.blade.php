@extends('layouts.app')

@section('title', 'Worksheets')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Worksheets</h1>
    <a href="{{ route('worksheets.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Create Worksheet
    </a>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6">
        <form method="GET" action="{{ route('worksheets.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="lesson_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lesson</label>
                <select name="lesson_id" id="lesson_id" class="block w-full text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
                    <option value="">All Lessons</option>
                    @foreach($lessons as $lesson)
                        <option value="{{ $lesson->id }}" {{ request('lesson_id') == $lesson->id ? 'selected' : '' }}>
                            {{ $lesson->title_english }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                <select name="type" id="type" class="block w-full text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
                    <option value="">All Types</option>
                    <option value="kanji_practice" {{ request('type') == 'kanji_practice' ? 'selected' : '' }}>Kanji Practice</option>
                    <option value="hiragana_practice" {{ request('type') == 'hiragana_practice' ? 'selected' : '' }}>Hiragana Practice</option>
                    <option value="katakana_practice" {{ request('type') == 'katakana_practice' ? 'selected' : '' }}>Katakana Practice</option>
                    <option value="vocabulary_review" {{ request('type') == 'vocabulary_review' ? 'selected' : '' }}>Vocabulary Review</option>
                    <option value="grammar_exercises" {{ request('type') == 'grammar_exercises' ? 'selected' : '' }}>Grammar Exercises</option>
                    <option value="reading_comprehension" {{ request('type') == 'reading_comprehension' ? 'selected' : '' }}>Reading Comprehension</option>
                </select>
            </div>
            
            <div>
                <label for="published" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select name="published" id="published" class="block w-full text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
                    <option value="">All Worksheets</option>
                    <option value="1" {{ request('published') == '1' ? 'selected' : '' }}>Published Only</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">&nbsp;</label>
                <div class="flex space-x-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Filter
                    </button>
                    <a href="{{ route('worksheets.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Worksheets Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($worksheets as $worksheet)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-2">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $worksheet->name }}</h3>
                        @if(!$worksheet->is_published)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                Draft
                            </span>
                        @endif
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        {{ $worksheet->type === 'kanji_practice' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 
                           ($worksheet->type === 'hiragana_practice' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                           ($worksheet->type === 'katakana_practice' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200')) }}">
                        {{ str_replace('_', ' ', ucfirst($worksheet->type)) }}
                    </span>
                </div>
                
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ $worksheet->lesson->title_english ?? 'Custom Worksheet' }}
                    @if($worksheet->lesson)
                        <span class="text-gray-400">• Lesson {{ $worksheet->lesson->chapter }}</span>
                    @endif
                </p>

                <div class="flex items-center justify-between">
                    <div class="flex space-x-2">
                        <a href="{{ route('worksheets.show', $worksheet) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium">
                            View Details
                        </a>
                        @if($worksheet->type === 'kanji_practice')
                            <span class="text-gray-300">•</span>
                            <a href="{{ route('worksheets.generate', $worksheet) }}" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 text-sm font-medium">
                                Preview & Print
                            </a>
                        @endif
                    </div>
                    
                    @if($worksheet->print_settings)
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $worksheet->print_settings['paper_size'] ?? 'A4' }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full">
            <div class="text-center py-12">
                <div class="text-6xl mb-4">📄</div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No worksheets found</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Get started by creating a new worksheet or adjust your filters.</p>
                <a href="{{ route('worksheets.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-700 text-white font-bold rounded-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create Your First Worksheet
                </a>
            </div>
        </div>
    @endforelse
</div>
@endsection
