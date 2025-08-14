@extends('layouts.app')

@section('title', 'Vocabulary')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Vocabulary</h1>
    <a href="{{ route('vocabulary.quiz.index') }}" 
       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Take Quiz
    </a>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6">
        <form method="GET" action="{{ route('vocabulary.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                <label for="part_of_speech" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Part of Speech</label>
                <select name="part_of_speech" id="part_of_speech" class="block w-full text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
                    <option value="">All Types</option>
                    <option value="noun" {{ request('part_of_speech') == 'noun' ? 'selected' : '' }}>Noun</option>
                    <option value="verb" {{ request('part_of_speech') == 'verb' ? 'selected' : '' }}>Verb</option>
                    <option value="adjective" {{ request('part_of_speech') == 'adjective' ? 'selected' : '' }}>Adjective</option>
                    <option value="adverb" {{ request('part_of_speech') == 'adverb' ? 'selected' : '' }}>Adverb</option>
                    <option value="particle" {{ request('part_of_speech') == 'particle' ? 'selected' : '' }}>Particle</option>
                    <option value="expression" {{ request('part_of_speech') == 'expression' ? 'selected' : '' }}>Expression</option>
                    <option value="affix" {{ request('part_of_speech') == 'affix' ? 'selected' : '' }}>Prefix/Suffix</option>
                    <option value="counter" {{ request('part_of_speech') == 'counter' ? 'selected' : '' }}>Counter</option>
                </select>
            </div>
            
            <div>
                <label for="jlpt_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">JLPT Level</label>
                <select name="jlpt_level" id="jlpt_level" class="block w-full text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
                    <option value="">All Levels</option>
                    <option value="N5" {{ request('jlpt_level') == 'N5' ? 'selected' : '' }}>N5</option>
                    <option value="N4" {{ request('jlpt_level') == 'N4' ? 'selected' : '' }}>N4</option>
                    <option value="N3" {{ request('jlpt_level') == 'N3' ? 'selected' : '' }}>N3</option>
                    <option value="N2" {{ request('jlpt_level') == 'N2' ? 'selected' : '' }}>N2</option>
                    <option value="N1" {{ request('jlpt_level') == 'N1' ? 'selected' : '' }}>N1</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">&nbsp;</label>
                <div class="flex space-x-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Filter
                    </button>
                    <a href="{{ route('vocabulary.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Clear
                    </a>
                </div>
            </div>
        </form>
        
        <div class="mt-4 flex space-x-2">
            <label class="inline-flex items-center">
                <input type="checkbox" {{ request('kanji_worksheet') == '1' ? 'checked' : '' }} 
                       onchange="toggleKanjiFilter(this)" class="rounded border-gray-300 text-purple-600 shadow-sm">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Kanji Practice Items Only</span>
            </label>
        </div>
    </div>
</div>

<!-- Vocabulary Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($vocabulary as $vocab)
    <a href="{{ route('vocabulary.show', $vocab->id) }}">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <div class="japanese-text text-gray-900 dark:text-gray-100 text-xl font-bold mb-2">
                            @if($vocab->word_furigana)
                                <x-furigana-text>{{ $vocab->furigana_word }}</x-furigana-text>
                            @else
                                {{ $vocab->japanese_word }}
                            @endif
                        </div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ $vocab->word_english }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ ucfirst($vocab->part_of_speech) }}</div>
                    </div>
                    
                    <div class="flex flex-col space-y-1">
                        @if($vocab->jlpt_level)
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $vocab->jlpt_level }}
                            </span>
                        @endif
                        @if($vocab->include_in_kanji_worksheet)
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                Kanji Practice
                            </span>
                        @endif
                    </div>
                </div>
                
                @if($vocab->example_sentences)
                    <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Example:</div>
                        <div class="japanese-text text-sm text-gray-600 dark:text-gray-400">
                            @if(is_array($vocab->example_sentences) && count($vocab->example_sentences) > 0)
                                {{ $vocab->example_sentences[0]['japanese'] ?? '' }}
                            @endif
                        </div>
                    </div>
                @endif
                
                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Lesson {{ $vocab->lesson->chapter }}
                </div>
            </div>
        </div>
        </a>
    @empty
        <div class="col-span-full">
            <div class="text-center py-12">
                <div class="text-6xl mb-4">🈂️</div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No vocabulary found</h3>
                <p class="text-gray-600 dark:text-gray-400">Try adjusting your filters or check back later for more content.</p>
            </div>
        </div>
    @endforelse
</div>

<script>
function toggleKanjiFilter(checkbox) {
    const url = new URL(window.location);
    if (checkbox.checked) {
        url.searchParams.set('kanji_worksheet', '1');
    } else {
        url.searchParams.delete('kanji_worksheet');
    }
    window.location = url;
}
</script>
@endsection 