@extends('layouts.app')

@section('title', $article->title . ' - Article')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="flex items-center space-x-4 mb-2">
                <a href="{{ route('lessons.show', $article->lesson->id) }}" class="text-blue-500 hover:text-blue-700">← Back to {{ $article->lesson->title_english }}</a>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                    📖 Article
                </span>
                <a href="{{ route('lessons.show', $article->lesson->id) }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    Lesson {{ $article->lesson->chapter }}
                </a>
            </div>
            
            <div class="mb-4">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                    {{ $article->title }}
                </h1>
                @if($article->subtitle)
                    <h2 class="text-xl text-gray-600 dark:text-gray-400">{{ $article->subtitle }}</h2>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-3 space-y-6">
        <!-- Article Content -->
        @if($article->hasMarkdownContent())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="prose dark:prose-invert max-w-none 
                        prose-headings:text-gray-900 dark:prose-headings:text-gray-100
                        prose-p:text-gray-700 dark:prose-p:text-gray-300
                        prose-strong:text-gray-900 dark:prose-strong:text-gray-100
                        prose-code:text-blue-600 dark:prose-code:text-blue-400
                        prose-code:bg-gray-100 dark:prose-code:bg-gray-800
                        prose-blockquote:border-blue-500 dark:prose-blockquote:border-blue-400
                        prose-blockquote:text-gray-700 dark:prose-blockquote:text-gray-300
                        prose-a:text-blue-600 dark:prose-a:text-blue-400 prose-a:hover:text-blue-800 dark:prose-a:hover:text-blue-200
                        prose-ul:text-gray-700 dark:prose-ul:text-gray-300
                        prose-ol:text-gray-700 dark:prose-ol:text-gray-300
                        prose-li:text-gray-700 dark:prose-li:text-gray-300">
                        {!! $article->getMarkdownContent() !!}
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-center text-gray-500 dark:text-gray-400">
                        <div class="text-6xl mb-4">📄</div>
                        <p class="text-lg font-medium mb-2">No content available</p>
                        <p class="text-sm">This article doesn't have any markdown content yet.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Covered Vocabulary -->
        @if($article->covered_vocabulary_ids && count($article->covered_vocabulary_ids) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">📝</span>
                        Vocabulary Covered ({{ count($article->covered_vocabulary_ids) }})
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($article->coveredVocabulary() as $vocab)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                <a href="{{ route('vocabulary.show', $vocab->id) }}" class="block">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-1">
                                            <div class="text-lg font-medium text-gray-900 dark:text-gray-100 japanese-text mb-1">
                                                @if($vocab->hasFurigana())
                                                    <x-furigana-text>{{ $vocab->furigana_word }}</x-furigana-text>
                                                @else
                                                    {{ $vocab->japanese_word }}
                                                @endif
                                            </div>
                                            <div class="text-gray-600 dark:text-gray-400">{{ $vocab->word_english }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                {{ ucfirst(str_replace('_', ' ', $vocab->part_of_speech)) }}
                                                @if($vocab->jlpt_level)
                                                    • {{ $vocab->jlpt_level }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-blue-500 hover:text-blue-700">
                                            →
                                        </div>
                                    </div>
                                </a>
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
                    <a href="{{ route('lessons.show', $article->lesson->id) }}" class="block w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                        View Lesson
                    </a>
                    @if($article->covered_vocabulary_ids && count($article->covered_vocabulary_ids) > 0)
                        <a href="{{ route('vocabulary.index', ['lesson_id' => $article->lesson->id]) }}" class="block w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                            Study Vocabulary
                        </a>
                    @endif
                    @if($article->lesson->questions->count() > 0)
                        <a href="{{ route('questions.index', ['lesson_id' => $article->lesson->id]) }}" class="block w-full bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded text-center">
                            Practice Questions
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Article Details -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Article Details</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Lesson</span>
                        <span class="font-semibold dark:text-white"><a href="{{ route('lessons.show', $article->lesson->id) }}">{{ $article->lesson->chapter }}</a></span>
                    </div>
                    
                    @if($article->covered_vocabulary_ids)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Vocabulary Items</span>
                            <span class="font-semibold dark:text-white">{{ count($article->covered_vocabulary_ids) }}</span>
                        </div>
                    @endif
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Created</span>
                        <span class="font-semibold dark:text-white">{{ $article->created_at->format('M j, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Articles -->
        @php
            $relatedArticles = $article->lesson->articles()->where('id', '!=', $article->id)->get();
        @endphp
        @if($relatedArticles->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">More from this Lesson</h3>
                    <div class="space-y-3">
                        @foreach($relatedArticles as $related)
                            <a href="{{ route('articles.show', $related->id) }}" class="block p-3 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">
                                    {{ $related->title }}
                                </div>
                                @if($related->subtitle)
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ $related->subtitle }}
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    .japanese-text {
        font-family: 'Noto Sans JP', 'Hiragino Sans', sans-serif;
    }
</style>

@endsection
