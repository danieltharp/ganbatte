@extends('layouts.app')

@section('title', 'Vocabulary Quiz')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6">Vocabulary Quiz</h1>
            
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Test your knowledge of Japanese vocabulary from the Minna no Nihongo lessons. 
                Choose your settings below to start a personalized quiz session.
            </p>

            <form method="POST" action="{{ route('vocabulary.quiz.start') }}" class="space-y-6">
                @csrf

                <!-- Lesson Range -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lesson Range</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="lesson_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                From Lesson
                            </label>
                            <select name="lesson_from" id="lesson_from" required
                                    class="block w-full text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
                                <option value="">Select starting lesson</option>
                                @foreach($lessons as $lesson)
                                    <option value="{{ $lesson->id }}" {{ old('lesson_from') == $lesson->id ? 'selected' : '' }}>
                                        Lesson {{ $lesson->chapter }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="lesson_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                To Lesson
                            </label>
                            <select name="lesson_to" id="lesson_to" required
                                    class="block w-full text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
                                <option value="">Select ending lesson</option>
                                @foreach($lessons as $lesson)
                                    <option value="{{ $lesson->id }}" {{ old('lesson_to') == $lesson->id ? 'selected' : '' }}>
                                        Lesson {{ $lesson->chapter }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Difficulty Level -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Difficulty Level</h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-start cursor-pointer">
                            <input type="radio" name="difficulty" value="easy" checked
                                   class="mt-1 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Easy (Multiple Choice)</span>
                                <span class="block text-sm text-gray-600 dark:text-gray-400">
                                    Choose the correct answer from four options
                                </span>
                            </div>
                        </label>
                        
                        <label class="flex items-start cursor-pointer">
                            <input type="radio" name="difficulty" value="hard"
                                   class="mt-1 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Hard (Type Answer)</span>
                                <span class="block text-sm text-gray-600 dark:text-gray-400">
                                    Type the correct answer without any hints
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Quiz Mode -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Quiz Mode</h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-start cursor-pointer">
                            <input type="radio" name="mode" value="recognition" checked
                                   class="mt-1 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Recognition (Japanese → English)</span>
                                <span class="block text-sm text-gray-600 dark:text-gray-400">
                                    Translate Japanese words to English
                                </span>
                            </div>
                        </label>
                        
                        <label class="flex items-start cursor-pointer">
                            <input type="radio" name="mode" value="recall"
                                   class="mt-1 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Recall (English → Japanese)</span>
                                <span class="block text-sm text-gray-600 dark:text-gray-400">
                                    Produce Japanese words from English prompts
                                </span>
                            </div>
                        </label>
                        
                        <label class="flex items-start cursor-pointer">
                            <input type="radio" name="mode" value="mixed"
                                   class="mt-1 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Mixed Mode</span>
                                <span class="block text-sm text-gray-600 dark:text-gray-400">
                                    A mix of both recognition and recall questions
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Number of Questions -->
                <div>
                    <label for="question_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Number of Questions (Optional)
                    </label>
                    <input type="number" name="question_count" id="question_count" min="5" max="50" 
                           placeholder="20 (default)"
                           class="block w-full text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Leave empty for default (20 questions or all available if less)
                    </p>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Submit Button -->
                <div class="flex justify-center pt-4">
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition-colors">
                        Start Quiz 🚀
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Start Options -->
    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Start Options</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button onclick="setQuickStart('beginner')" 
                        class="p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="text-2xl mb-2">🌱</div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">Beginner</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Lessons 1-5, Easy</div>
                </button>
                
                <button onclick="setQuickStart('intermediate')" 
                        class="p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="text-2xl mb-2">🎯</div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">Intermediate</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Lessons 6-15, Mixed</div>
                </button>
                
                <button onclick="setQuickStart('advanced')" 
                        class="p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="text-2xl mb-2">🔥</div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">Advanced</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">All Lessons, Hard</div>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function setQuickStart(level) {
    const lessonOptions = document.querySelectorAll('#lesson_from option, #lesson_to option');
    const lessons = Array.from(lessonOptions).filter(opt => opt.value);
    
    if (lessons.length === 0) return;
    
    switch(level) {
        case 'beginner':
            // Set first 5 lessons if available
            document.getElementById('lesson_from').value = lessons[0]?.value || '';
            document.getElementById('lesson_to').value = lessons[Math.min(4, lessons.length - 1)]?.value || '';
            document.querySelector('input[name="difficulty"][value="easy"]').checked = true;
            document.querySelector('input[name="mode"][value="recognition"]').checked = true;
            break;
            
        case 'intermediate':
            // Set lessons 6-15 if available
            const startIdx = Math.min(5, lessons.length - 1);
            const endIdx = Math.min(14, lessons.length - 1);
            document.getElementById('lesson_from').value = lessons[startIdx]?.value || '';
            document.getElementById('lesson_to').value = lessons[endIdx]?.value || '';
            document.querySelector('input[name="difficulty"][value="easy"]').checked = true;
            document.querySelector('input[name="mode"][value="mixed"]').checked = true;
            break;
            
        case 'advanced':
            // Set all lessons
            document.getElementById('lesson_from').value = lessons[0]?.value || '';
            document.getElementById('lesson_to').value = lessons[lessons.length - 1]?.value || '';
            document.querySelector('input[name="difficulty"][value="hard"]').checked = true;
            document.querySelector('input[name="mode"][value="mixed"]').checked = true;
            break;
    }
}

// Validate lesson range on form submit
document.querySelector('form').addEventListener('submit', function(e) {
    const fromSelect = document.getElementById('lesson_from');
    const toSelect = document.getElementById('lesson_to');
    
    if (fromSelect.selectedIndex > toSelect.selectedIndex && toSelect.selectedIndex > 0) {
        e.preventDefault();
        alert('The ending lesson must be after or equal to the starting lesson.');
    }
});
</script>
@endsection
