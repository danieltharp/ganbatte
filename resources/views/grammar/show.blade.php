@extends('layouts.app')

@section('title', $grammarPoint->name_english)

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="flex items-center space-x-4 mb-2">
                <a href="{{ route('grammar.index') }}" class="text-blue-500 hover:text-blue-700">← Back to Grammar</a>
                @if($grammarPoint->lesson)
                    <a href="{{ route('lessons.show', $grammarPoint->lesson->id) }}" class="text-green-500 hover:text-green-700">
                        Lesson {{ $grammarPoint->lesson->chapter }}
                    </a>
                @endif
                @if($grammarPoint->jlpt_level)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($grammarPoint->jlpt_level === 'N5') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($grammarPoint->jlpt_level === 'N4') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                        @elseif($grammarPoint->jlpt_level === 'N3') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @elseif($grammarPoint->jlpt_level === 'N2') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                        {{ $grammarPoint->jlpt_level }}
                    </span>
                @endif
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                @if($grammarPoint->name_japanese)
                    <div class="japanese-text text-4xl mb-2">
                        @if($grammarPoint->name_furigana)
                            <x-furigana-text>{{ $grammarPoint->name_furigana }}</x-furigana-text>
                        @else
                            {{ $grammarPoint->name_japanese }}
                        @endif
                    </div>
                @endif
                {{ $grammarPoint->name_english }}
            </h1>
        </div>
    </div>
</div>

<!-- Grammar Content -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-3 space-y-6">
        <!-- Pattern -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Pattern</h2>
                <div class="japanese-text text-2xl font-mono bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border-l-4 border-blue-500 text-gray-900 dark:text-gray-100">
                    {{ $grammarPoint->pattern }}
                </div>
            </div>
        </div>

        <!-- Usage -->
        @if($grammarPoint->usage)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Usage</h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {{ $grammarPoint->usage }}
                    </p>
                </div>
            </div>
        @endif

        <!-- Explanation -->
        @if($grammarPoint->getExplanationContent())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Explanation</h2>
                        @if($grammarPoint->isMarkdownExplanation())
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                📝 Rich Format
                            </span>
                        @endif
                    </div>
                    <div class="prose dark:prose-invert max-w-none 
                        prose-headings:text-gray-900 dark:prose-headings:text-gray-100
                        prose-p:text-gray-700 dark:prose-p:text-gray-300
                        prose-strong:text-gray-900 dark:prose-strong:text-gray-100
                        prose-code:text-blue-600 dark:prose-code:text-blue-400
                        prose-code:bg-gray-100 dark:prose-code:bg-gray-800
                        prose-blockquote:border-blue-500 dark:prose-blockquote:border-blue-400
                        prose-blockquote:text-gray-700 dark:prose-blockquote:text-gray-300">
                        {!! $grammarPoint->getExplanationContent() !!}
                    </div>
                </div>
            </div>
        @endif

        <!-- Examples -->
        @if($grammarPoint->examples && count($grammarPoint->examples) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Examples ({{ count($grammarPoint->examples) }})
                    </h2>
                    <div class="space-y-4">
                        @foreach($grammarPoint->examples as $example)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">
                                @if(isset($example['sentence']['japanese']))
                                    <div class="japanese-text text-lg mb-2 font-medium text-gray-900 dark:text-gray-100">
                                        @if(isset($example['sentence']['furigana']))
                                            <x-furigana-text>{{ $example['sentence']['furigana'] }}</x-furigana-text>
                                        @else
                                            {{ $example['sentence']['japanese'] }}
                                        @endif
                                    </div>
                                @endif
                                
                                @if(isset($example['sentence']['english']))
                                    <div class="text-gray-900 dark:text-gray-100 mb-2">
                                        {{ $example['sentence']['english'] }}
                                    </div>
                                @endif
                                
                                @if(isset($example['explanation']))
                                    <div class="text-sm text-gray-600 dark:text-gray-400 italic">
                                        {{ $example['explanation'] }}
                                    </div>
                                @endif
                                
                                @if(isset($example['audio']))
                                    <div class="mt-2">
                                        <button class="text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-2 py-1 rounded">
                                            🔊 Play Audio
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Related Questions -->
        @if($grammarPoint->questions && $grammarPoint->questions->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            Practice Questions ({{ $grammarPoint->questions->count() }})
                        </h2>
                        <a href="{{ route('questions.index', ['grammar_id' => $grammarPoint->id]) }}" class="text-blue-500 hover:text-blue-700 text-sm">
                            Practice All →
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @php
                            $questionTypes = $grammarPoint->questions->groupBy('type');
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
                    @if($grammarPoint->lesson)
                        <a href="{{ route('lessons.show', $grammarPoint->lesson->id) }}" class="block w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                            View Lesson
                        </a>
                    @endif
                    @if($grammarPoint->questions && $grammarPoint->questions->count() > 0)
                        <a href="{{ route('questions.index', ['grammar_id' => $grammarPoint->id]) }}" class="block w-full bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded text-center">
                            Practice Questions
                        </a>
                    @endif
                    <a href="{{ route('grammar.index', ['jlpt_level' => $grammarPoint->jlpt_level]) }}" class="block w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                        Similar Level Grammar
                    </a>
                </div>
            </div>
        </div>

        <!-- Grammar Stats -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Details</h3>
                <div class="space-y-3">
                    @if($grammarPoint->lesson)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Lesson</span>
                            <span class="font-semibold dark:text-white">{{ $grammarPoint->lesson->chapter }}</span>
                        </div>
                    @endif
                    @if($grammarPoint->jlpt_level)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">JLPT Level</span>
                            <span class="font-semibold dark:text-white">{{ $grammarPoint->jlpt_level }}</span>
                        </div>
                    @endif
                    @if($grammarPoint->examples)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Examples</span>
                            <span class="font-semibold dark:text-white">{{ count($grammarPoint->examples) }}</span>
                        </div>
                    @endif
                    @if($grammarPoint->questions)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Questions</span>
                            <span class="font-semibold dark:text-white">{{ $grammarPoint->questions->count() }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Related Grammar -->
        @if($grammarPoint->related_grammar && count($grammarPoint->related_grammar) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Related Grammar</h3>
                    <div class="space-y-2">
                        @foreach($grammarPoint->related_grammar as $relatedId)
                            @php
                                $related = \App\Models\GrammarPoint::find($relatedId);
                            @endphp
                            @if($related)
                                <a href="{{ route('grammar.show', $related->id) }}" class="block p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $related->name_english }}
                                    </div>
                                    @if($related->name_japanese)
                                        <div class="text-xs japanese-text text-gray-600 dark:text-gray-400">
                                            {{ $related->name_japanese }}
                                        </div>
                                    @endif
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection 