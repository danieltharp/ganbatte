<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ganbatte - Japanese Learning with Minna No Nihongo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            <span class="text-3xl">頑張って</span>
                            <span class="text-blue-600 dark:text-blue-400 ml-2">Ganbatte</span>
                        </h1>
                    </div>
                    
                    @if (Route::has('login'))
                        <nav class="flex space-x-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 font-medium">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Get Started
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                <div class="mb-8">
                    <h2 class="text-4xl md:text-6xl font-bold text-gray-900 dark:text-white mb-4">
                        Learn Japanese with
                        <span class="text-blue-600 dark:text-blue-400">頑張って</span>
                    </h2>
                    <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">
                        Your comprehensive companion for studying Minna No Nihongo textbook series
                    </p>
                </div>

                <!-- Features Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                    <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-6 shadow-lg">
                        <div class="text-4xl mb-4">📚</div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Structured Lessons</h3>
                        <p class="text-gray-600 dark:text-gray-300">Follow along with the textbook with organized vocabulary, grammar, and practice.</p>
                    </div>
                    
                    <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-6 shadow-lg">
                        <div class="text-4xl mb-4">🈂️</div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Interactive Practice</h3>
                        <p class="text-gray-600 dark:text-gray-300">Practice with furigana support, audio pronunciations, and varied question types.</p>
                    </div>
                    
                    <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-xl p-6 shadow-lg">
                        <div class="text-4xl mb-4">📝</div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Kanji Worksheets</h3>
                        <p class="text-gray-600 dark:text-gray-300">Generate printable worksheets for kanji handwriting practice.</p>
                    </div>
                </div>

                <!-- Stats -->
                <div class="bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-xl p-8 mb-8">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ \App\Models\Lesson::count() }}</div>
                            <div class="text-gray-600 dark:text-gray-400">Lessons</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ \App\Models\Vocabulary::count() }}</div>
                            <div class="text-gray-600 dark:text-gray-400">Vocabulary</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ \App\Models\Vocabulary::forKanjiWorksheet()->count() }}</div>
                            <div class="text-gray-600 dark:text-gray-400">Kanji Practice</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ \App\Models\Question::count() }}</div>
                            <div class="text-gray-600 dark:text-gray-400">Questions</div>
                        </div>
                    </div>
                </div>

                <!-- CTA -->
                <div class="space-y-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition-colors">
                            Go to Dashboard
                        </a>
                    @else
                        <div class="space-x-4">
                            <a href="{{ route('register') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition-colors">
                                Start Learning
                            </a>
                            <a href="{{ route('lessons.index') }}" class="inline-block bg-white hover:bg-gray-50 text-blue-600 font-bold py-3 px-8 rounded-lg text-lg border border-blue-600 transition-colors">
                                Browse Lessons
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border-t border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="text-center text-gray-600 dark:text-gray-400">
                    <p>&copy; {{ date('Y') }} Ganbatte. A companion site for Minna No Nihongo learners.</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
