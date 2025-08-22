@extends('layouts.app')

@section('title', 'Kanji Practice Worksheets')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Kanji Practice Worksheets</h1>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6">
        <form method="GET" action="{{ route('vocabulary.kanji-worksheet') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="lesson_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lesson</label>
                <select name="lesson_id" id="lesson_id" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
                    <option value="">All Lessons</option>
                    @foreach($lessons as $lesson)
                        <option value="{{ $lesson->id }}" {{ request('lesson_id') == $lesson->id ? 'selected' : '' }}>
                            Chapter {{ $lesson->chapter }}: {{ $lesson->title_english }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded mr-2">
                    Filter
                </button>
                <a href="{{ route('vocabulary.kanji-worksheet') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Info Box -->
<div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4 mb-6">
    <div class="flex">
        <div class="text-2xl mr-3">📝</div>
        <div>
            <h3 class="text-lg font-semibold text-purple-900 dark:text-purple-100 mb-2">Kanji Practice Items</h3>
            <p class="text-purple-700 dark:text-purple-300 text-sm">
                These vocabulary items contain kanji characters suitable for handwriting practice. 
                Items with only hiragana or katakana are excluded from worksheet generation.
            </p>
        </div>
    </div>
</div>

<!-- Kanji Vocabulary Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @forelse($vocabulary as $vocab)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow border-l-4 border-purple-500">
            <div class="p-4">
                <div class="text-center">
                    <div class="japanese-text text-3xl font-bold mb-3">
                        @if($vocab->word_furigana)
                            <x-furigana-text>{{ $vocab->furigana_word }}</x-furigana-text>
                        @else
                            {{ $vocab->japanese_word }}
                        @endif
                    </div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $vocab->word_english }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        @if($vocab->part_of_speech && is_array($vocab->part_of_speech))
                            {{ collect($vocab->part_of_speech)->map(fn($pos) => ucfirst(str_replace('_', ' ', $pos)))->join(', ') }}
                        @endif
                    </div>
                    
                    @if($vocab->jlpt_level)
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ $vocab->jlpt_level }}
                        </span>
                    @endif
                </div>
                
                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400 text-center">
                    Lesson {{ $vocab->lesson->chapter }}
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full">
            <div class="text-center py-12">
                <div class="text-6xl mb-4">📝</div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No kanji practice items found</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    @if(request('lesson_id'))
                        Try selecting a different lesson or clear the filter to see all available items.
                    @else
                        Kanji practice items will appear here when lessons with kanji vocabulary are added.
                    @endif
                </p>
            </div>
        </div>
    @endforelse
</div>

@if($vocabulary->count() > 0)
    <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Worksheet Generation</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Found <strong>{{ $vocabulary->count() }}</strong> kanji vocabulary items suitable for handwriting practice.
            </p>
            <div class="flex space-x-4">
                <button onclick="window.print()" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    🖨️ Print Worksheet
                </button>
                <button onclick="generatePDF()" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    📄 Generate PDF
                </button>
            </div>
        </div>
    </div>
@endif

<style>
@media print {
    .no-print { display: none !important; }
    .japanese-text { font-size: 24px !important; }
    .grid { grid-template-columns: repeat(3, 1fr) !important; }
}
</style>

<script>
function generatePDF() {
    alert('PDF generation feature coming soon! For now, please use the print function.');
}
</script>
@endsection 