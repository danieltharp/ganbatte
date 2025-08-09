@extends('layouts.app')

@section('title', $section->name . ' - ' . $section->lesson->title_english)

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="flex items-center space-x-4 mb-2">
                <a href="{{ route('lessons.show', $section->lesson->id) }}" class="text-blue-500 hover:text-blue-700">← Back to {{ $section->lesson->title_english }}</a>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    {{ ucfirst(str_replace('_', ' ', $section->section_type)) }}
                </span>
                @if($section->estimated_duration_minutes)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                        {{ $section->estimated_duration_minutes }} min
                    </span>
                @endif
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                <span class="japanese-text text-4xl mb-2">{{ $section->name }}</span>
            </h1>
            
            @if($section->purpose)
                <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $section->purpose }}</p>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-3 space-y-6">
        <!-- Audio Player -->
        @if($section->audio_filename)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">🎧</span>
                        Audio Practice
                    </h2>
                    
                    <x-audio-player :audio-file="$section->audio_filename" />
                </div>
            </div>
        @endif

        <!-- Instructions -->
        @if($section->instructions)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">📋</span>
                        Instructions
                    </h2>
                    
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $section->instructions }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Related Content -->
        @if($section->related_vocabulary_ids && count($section->related_vocabulary_ids) > 0 || $section->related_grammar_ids && count($section->related_grammar_ids) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">📚</span>
                        Related Content
                    </h2>
                    
                    <div class="space-y-4">
                        @if($section->related_vocabulary_ids && count($section->related_vocabulary_ids) > 0)
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Related Vocabulary</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($section->related_vocabulary_ids as $vocabId)
                                        @php
                                            $vocab = $section->lesson->vocabulary->where('unique_id', $vocabId)->first();
                                        @endphp
                                        @if($vocab)
                                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                <span class="japanese-text mr-1">{{ $vocab->japanese_word }}</span>
                                                {{ $vocab->word_english }}
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($section->related_grammar_ids && count($section->related_grammar_ids) > 0)
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Related Grammar</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($section->related_grammar_ids as $grammarId)
                                        @php
                                            $grammar = $section->lesson->grammarPoints->where('unique_id', $grammarId)->first();
                                        @endphp
                                        @if($grammar)
                                            <a href="{{ route('grammar.show', $grammar->id) }}" class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                                                @if($grammar->name_japanese)
                                                    <span class="japanese-text mr-1">{{ $grammar->name_japanese }}</span>
                                                @endif
                                                {{ $grammar->name_english }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Prerequisites -->
        @if($section->prerequisites && count($section->prerequisites) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">⚠️</span>
                        Prerequisites
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($section->prerequisites as $prerequisiteId)
                            @php
                                $prerequisiteSection = $section->lesson->sections->where('id', $prerequisiteId)->first();
                            @endphp
                            @if($prerequisiteSection)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <a href="{{ route('sections.show', $prerequisiteSection->id) }}" class="flex items-center space-x-2 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                        <span class="text-sm font-medium">{{ $prerequisiteSection->name }}</span>
                                    </a>
                                </div>
                            @else
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $prerequisiteId }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Section Info -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Section Details</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Type</span>
                        <span class="font-semibold dark:text-white">{{ ucfirst(str_replace('_', ' ', $section->section_type)) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Page</span>
                        <span class="font-semibold dark:text-white">{{ $section->page_number }}</span>
                    </div>
                    @if($section->page_section)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Section</span>
                            <span class="font-semibold dark:text-white">{{ ucfirst($section->page_section) }}</span>
                        </div>
                    @endif
                    @if($section->estimated_duration_minutes)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Time</span>
                            <span class="font-semibold dark:text-white">{{ $section->estimated_duration_minutes }} min</span>
                        </div>
                    @endif
                    @if($section->completion_trackable)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Trackable</span>
                            <span class="font-semibold text-green-600 dark:text-green-400">Yes</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('lessons.show', $section->lesson->id) }}" class="block w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                        View Lesson
                    </a>
                    @if($section->related_vocabulary_ids && count($section->related_vocabulary_ids) > 0)
                        <a href="{{ route('vocabulary.index', ['lesson_id' => $section->lesson->id]) }}" class="block w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                            Study Vocabulary
                        </a>
                    @endif
                    @if($section->related_grammar_ids && count($section->related_grammar_ids) > 0)
                        <a href="{{ route('grammar.index', ['lesson_id' => $section->lesson->id]) }}" class="block w-full bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center">
                            Review Grammar
                        </a>
                    @endif
                    @if($section->completion_trackable)
                        <button class="block w-full bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded text-center" onclick="markComplete()">
                            Mark Complete
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Navigation -->
        @php
            $allSections = $section->lesson->sections()->orderBy('order_weight')->get();
            $currentIndex = $allSections->search(function($item) use ($section) {
                return $item->id === $section->id;
            });
            $prevSection = $currentIndex > 0 ? $allSections[$currentIndex - 1] : null;
            $nextSection = $currentIndex < $allSections->count() - 1 ? $allSections[$currentIndex + 1] : null;
        @endphp
        @if($prevSection || $nextSection)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Navigate</h3>
                    <div class="space-y-3">
                        @if($prevSection)
                            <a href="{{ route('sections.show', $prevSection->id) }}" class="flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                <span class="mr-2">←</span>
                                <div>
                                    <div class="text-sm font-medium">Previous</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">{{ $prevSection->name }}</div>
                                </div>
                            </a>
                        @endif
                        @if($nextSection)
                            <a href="{{ route('sections.show', $nextSection->id) }}" class="flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                <div class="flex-1">
                                    <div class="text-sm font-medium">Next</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">{{ $nextSection->name }}</div>
                                </div>
                                <span class="ml-2">→</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function markComplete() {
    // This would typically make an AJAX call to mark the section as complete
    // For now, just show a simple alert
    alert('Section marked as complete!');
    
    // In a real implementation, you might want to:
    // 1. Make an AJAX call to update user progress
    // 2. Update the UI to show completion status
    // 3. Possibly redirect to the next section
}
</script>
@endpush

@endsection
