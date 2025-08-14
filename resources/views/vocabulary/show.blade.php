@extends('layouts.app')

@section('title', $vocabulary->word_english . ' - Vocabulary')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="flex items-center space-x-4 mb-2">
                <a href="{{ route('vocabulary.index') }}" class="text-blue-500 hover:text-blue-700">← Back to Vocabulary</a>
                @if($vocabulary->lesson)
                    <a href="{{ route('lessons.show', $vocabulary->lesson->id) }}" class="text-blue-500 hover:text-blue-700">
                        {{ $vocabulary->lesson->title_english }}
                    </a>
                @endif
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    {{ ucfirst(str_replace('_', ' ', $vocabulary->part_of_speech)) }}
                </span>
                @if($vocabulary->jlpt_level)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        @if($vocabulary->jlpt_level === 'N5') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($vocabulary->jlpt_level === 'N4') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @elseif($vocabulary->jlpt_level === 'N3') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                        @elseif($vocabulary->jlpt_level === 'N2') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                        @else bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 @endif">
                        {{ $vocabulary->jlpt_level }}
                    </span>
                @endif
            </div>
            
            <div class="mb-4">
                <h1 class="text-5xl font-bold text-gray-900 dark:text-gray-100 japanese-text mb-2">
                    @if($vocabulary->hasFurigana())
                        <x-furigana-text>{{ $vocabulary->furigana_word }}</x-furigana-text>
                    @else
                        {{ $vocabulary->japanese_word }}
                    @endif
                </h1>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300">{{ $vocabulary->word_english }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-3 space-y-6">
        <!-- Audio Player -->
        @if($vocabulary->audio_filename)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">🎧</span>
                        Pronunciation
                    </h2>
                    
                    <x-audio-player :audio-file="$vocabulary->audio_filename" />
                    
                    @if($vocabulary->audio_speaker)
                        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            <strong>Speaker:</strong> {{ $vocabulary->audio_speaker }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Conjugations (for verbs and adjectives) -->
        @if($vocabulary->conjugations && count($vocabulary->conjugations) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">🔄</span>
                        Conjugations
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($vocabulary->conjugations as $form => $conjugation)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">
                                <div class="font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    {{ ucfirst(str_replace('_', ' ', $form)) }}
                                </div>
                                <div class="japanese-text text-lg text-gray-800 dark:text-gray-200 font-medium">
                                    @if(is_array($conjugation) && isset($conjugation['furigana']))
                                        <x-furigana-text>{{ $conjugation['furigana'] }}</x-furigana-text>
                                    @elseif(is_array($conjugation) && isset($conjugation['japanese']))
                                        {{ $conjugation['japanese'] }}
                                    @else
                                        {{ $conjugation }}
                                    @endif
                                </div>
                                @if(is_array($conjugation) && isset($conjugation['english']))
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $conjugation['english'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Example Sentences -->
        @if($vocabulary->example_sentences && count($vocabulary->example_sentences) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">📝</span>
                        Example Sentences ({{ count($vocabulary->example_sentences) }})
                    </h2>
                    
                    <div class="space-y-4">
                        @foreach($vocabulary->example_sentences as $example)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">
                                @if(isset($example['japanese']))
                                    <div class="japanese-text text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                        @if(isset($example['furigana']))
                                            <x-furigana-text>{{ $example['furigana'] }}</x-furigana-text>
                                        @else
                                            {{ $example['japanese'] }}
                                        @endif
                                    </div>
                                @endif
                                @if(isset($example['english']))
                                    <div class="text-gray-700 dark:text-gray-300 mb-2">{{ $example['english'] }}</div>
                                @endif
                                @if(isset($example['note']))
                                    <div class="text-sm text-gray-600 dark:text-gray-400 italic">{{ $example['note'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Notes -->
        @if($vocabulary->hasMarkdownNote())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                            <span class="mr-2">📝</span>
                            Notes
                        </h2>
                    </div>
                    <div class="prose dark:prose-invert max-w-none 
                        prose-headings:text-gray-900 dark:prose-headings:text-gray-100
                        prose-p:text-gray-700 dark:prose-p:text-gray-300
                        prose-strong:text-gray-900 dark:prose-strong:text-gray-100
                        prose-code:text-blue-600 dark:prose-code:text-blue-400
                        prose-code:bg-gray-100 dark:prose-code:bg-gray-800
                        prose-blockquote:border-blue-500 dark:prose-blockquote:border-blue-400
                        prose-blockquote:text-gray-700 dark:prose-blockquote:text-gray-300
                        prose-a:text-blue-600 dark:prose-a:text-blue-400 prose-a:hover:text-blue-800 dark:prose-a:hover:text-blue-200">
                        {!! $vocabulary->getMarkdownNote() !!}
                    </div>
                </div>
            </div>
        @endif

        <!-- Mnemonics -->
        @if($vocabulary->mnemonics && count($vocabulary->mnemonics) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">🧠</span>
                        Memory Aids
                    </h2>
                    
                    <div class="space-y-3">
                        @foreach($vocabulary->mnemonics as $mnemonic)
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <div class="text-gray-800 dark:text-gray-200">{{ $mnemonic }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Related Words -->
        @if($vocabulary->related_words && count($vocabulary->related_words) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">🔗</span>
                        Related Words ({{ count($vocabulary->related_words) }})
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($vocabulary->related_words as $related)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">
                                @if(isset($related['japanese']))
                                    <div class="japanese-text font-medium text-gray-900 dark:text-gray-100 mb-1">
                                        @if(isset($related['furigana']))
                                            <x-furigana-text>{{ $related['furigana'] }}</x-furigana-text>
                                        @else
                                            {{ $related['japanese'] }}
                                        @endif
                                    </div>
                                @endif
                                @if(isset($related['english']))
                                    <div class="text-gray-700 dark:text-gray-300 mb-1">{{ $related['english'] }}</div>
                                @endif
                                @if(isset($related['relationship']))
                                    <div class="text-xs text-gray-500 dark:text-gray-500 italic">{{ $related['relationship'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Tags -->
        @if($vocabulary->tags && count($vocabulary->tags) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">🏷️</span>
                        Tags
                    </h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($vocabulary->tags as $tag)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-gray-200">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Related Articles -->
        @php
            $relatedArticles = \App\Models\Article::coveringVocabulary($vocabulary->id);
        @endphp
        @if($relatedArticles->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">📖</span>
                        Related Articles ({{ $relatedArticles->count() }})
                    </h2>
                    
                    <div class="space-y-3">
                        @foreach($relatedArticles as $article)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                            <a href="{{ route('articles.show', $article->id) }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ $article->title }}</a>
                                        </h3>
                                        @if($article->subtitle)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $article->subtitle }}</p>
                                        @endif
                                        
                                        <div class="flex items-center space-x-3 text-sm text-gray-500 dark:text-gray-400">
                                            <span>📚 {{ $article->lesson->title_english }}</span>
                                        </div>
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
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    @if($vocabulary->lesson)
                        <a href="{{ route('lessons.show', $vocabulary->lesson->id) }}" class="block w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                            View Lesson
                        </a>
                        
                        <a href="{{ route('vocabulary.index', ['lesson_id' => $vocabulary->lesson->id]) }}" class="block w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                            Lesson Vocabulary
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Word Details -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Details</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Part of Speech</span>
                        <span class="font-semibold dark:text-white">{{ ucfirst(str_replace('_', ' ', $vocabulary->part_of_speech)) }}</span>
                    </div>
                    
                    @if($vocabulary->verb_type)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Verb Type</span>
                            <span class="font-semibold dark:text-white">{{ ucfirst(str_replace('_', ' ', $vocabulary->verb_type)) }}</span>
                        </div>
                    @endif
                    
                    @if($vocabulary->adjective_type)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Adjective Type</span>
                            <span class="font-semibold dark:text-white">{{ ucfirst(str_replace('_', ' ', $vocabulary->adjective_type)) }}</span>
                        </div>
                    @endif
                    
                    @if($vocabulary->jlpt_level)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">JLPT Level</span>
                            <span class="font-semibold dark:text-white">{{ $vocabulary->jlpt_level }}</span>
                        </div>
                    @endif
                    
                    @if($vocabulary->frequency_rank)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Frequency Rank</span>
                            <span class="font-semibold dark:text-white">#{{ number_format($vocabulary->frequency_rank) }}</span>
                        </div>
                    @endif
                    
                    @if($vocabulary->pitch_accent)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Pitch Accent</span>
                            <span class="font-semibold dark:text-white">{{ $vocabulary->pitch_accent }}</span>
                        </div>
                    @endif
                    
                    @if($vocabulary->include_in_kanji_worksheet)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Kanji Practice</span>
                            <span class="font-semibold text-green-600 dark:text-green-400">Included</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Also Accepted -->
        @if($vocabulary->also_accepted && (isset($vocabulary->also_accepted['japanese']) || isset($vocabulary->also_accepted['english'])))
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Also Accepted</h3>
                    <div class="space-y-3">
                        @if(isset($vocabulary->also_accepted['japanese']) && count($vocabulary->also_accepted['japanese']) > 0)
                            <div>
                                <span class="text-sm text-gray-600 dark:text-gray-400 block mb-2">Japanese variants:</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($vocabulary->also_accepted['japanese'] as $variant)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-sm bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 japanese-text">
                                            {{ $variant }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        @if(isset($vocabulary->also_accepted['english']) && count($vocabulary->also_accepted['english']) > 0)
                            <div>
                                <span class="text-sm text-gray-600 dark:text-gray-400 block mb-2">English variants:</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($vocabulary->also_accepted['english'] as $variant)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-sm bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                            {{ $variant }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Navigation -->
        @if($vocabulary->lesson)
            @php
                $lessonVocab = $vocabulary->lesson->vocabulary()->orderBy('id')->get();
                $currentIndex = $lessonVocab->search(function($item) use ($vocabulary) {
                    return $item->id === $vocabulary->id;
                });
                $prevVocab = $currentIndex !== false && $currentIndex > 0 ? $lessonVocab[$currentIndex - 1] : null;
                $nextVocab = $currentIndex !== false && $currentIndex < $lessonVocab->count() - 1 ? $lessonVocab[$currentIndex + 1] : null;
            @endphp
            @if($prevVocab || $nextVocab)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Navigate</h3>
                        <div class="space-y-3">
                            @if($prevVocab)
                                <a href="{{ route('vocabulary.show', $prevVocab->id) }}" class="flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                    <span class="mr-2">←</span>
                                    <div>
                                        <div class="text-sm font-medium">Previous</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400 japanese-text">{{ $prevVocab->japanese_word }} - {{ $prevVocab->word_english }}</div>
                                    </div>
                                </a>
                            @endif
                            @if($nextVocab)
                                <a href="{{ route('vocabulary.show', $nextVocab->id) }}" class="flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                    <div class="flex-1">
                                        <div class="text-sm font-medium">Next</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400 japanese-text">{{ $nextVocab->japanese_word }} - {{ $nextVocab->word_english }}</div>
                                    </div>
                                    <span class="ml-2">→</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

<style>
    .japanese-text {
        font-family: 'Noto Sans JP', 'Hiragino Sans', sans-serif;
    }
</style>

@endsection
