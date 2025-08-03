@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold mb-2">{{ __('Welcome to Ganbatte!') }}</h1>
                        <p class="text-gray-600 dark:text-gray-400">{{ __('Your Japanese learning companion for Minna No Nihongo') }}</p>
                    </div>
                    <div class="text-6xl">📚</div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="text-3xl mr-4">📖</div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ \App\Models\Lesson::count() }}</p>
                            <p class="text-gray-600 dark:text-gray-400">Lessons</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="text-3xl mr-4">🈂️</div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ \App\Models\Vocabulary::count() }}</p>
                            <p class="text-gray-600 dark:text-gray-400">Vocabulary</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="text-3xl mr-4">📝</div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ \App\Models\Vocabulary::forKanjiWorksheet()->count() }}</p>
                            <p class="text-gray-600 dark:text-gray-400">Kanji Practice</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="text-3xl mr-4">❓</div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ \App\Models\Question::count() }}</p>
                            <p class="text-gray-600 dark:text-gray-400">Questions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Navigation Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Lessons -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lessons</h3>
                        <div class="text-3xl">📖</div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Browse and study structured lessons from Minna No Nihongo textbook series.</p>
                    <div class="space-y-2">
                        <a href="{{ route('lessons.index') }}" class="block w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center transition-colors">
                            View All Lessons
                        </a>
                    </div>
                </div>
            </div>

            <!-- Vocabulary -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Vocabulary</h3>
                        <div class="text-3xl">🈂️</div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Learn new words with furigana, definitions, and example sentences.</p>
                    <div class="space-y-2">
                        <a href="{{ route('vocabulary.index') }}" class="block w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center transition-colors">
                            Browse Vocabulary
                        </a>
                        <a href="{{ route('vocabulary.kanji-worksheet') }}" class="block w-full bg-green-600 hover:bg-green-800 text-white font-bold py-2 px-4 rounded text-center transition-colors">
                            Kanji Practice
                        </a>
                    </div>
                </div>
            </div>

            <!-- Grammar -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Grammar</h3>
                        <div class="text-3xl">🔤</div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Master Japanese grammar patterns with detailed explanations.</p>
                    <div class="space-y-2">
                        <a href="{{ route('grammar.index') }}" class="block w-full bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center transition-colors">
                            Study Grammar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Practice -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Practice</h3>
                        <div class="text-3xl">✏️</div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Practice with interactive questions and exercises.</p>
                    <div class="space-y-2">
                        <a href="{{ route('questions.index') }}" class="block w-full bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded text-center transition-colors">
                            Start Practice
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tests -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tests</h3>
                        <div class="text-3xl">📋</div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Take comprehensive tests to assess your progress.</p>
                    <div class="space-y-2">
                        <a href="{{ route('tests.index') }}" class="block w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-center transition-colors">
                            View Tests
                        </a>
                    </div>
                </div>
            </div>

            <!-- Worksheets -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Worksheets</h3>
                        <div class="text-3xl">📄</div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Download printable worksheets for offline practice.</p>
                    <div class="space-y-2">
                        <a href="{{ route('worksheets.index') }}" class="block w-full bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-center transition-colors">
                            Browse Worksheets
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Lessons</h3>
                <div class="space-y-3">
                    @forelse(\App\Models\Lesson::orderBy('chapter')->limit(5)->get() as $lesson)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                    @if($lesson->title_japanese)
                                        <x-furigana-text>{{ $lesson->furigana_title }}</x-furigana-text>
                                    @endif
                                    {{ $lesson->title_english }}
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Chapter {{ $lesson->chapter }}</p>
                            </div>
                            <a href="{{ route('lessons.show', $lesson) }}" class="text-blue-500 hover:text-blue-700">
                                View →
                            </a>
                        </div>
                    @empty
                        <p class="text-gray-600 dark:text-gray-400">No lessons available yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
