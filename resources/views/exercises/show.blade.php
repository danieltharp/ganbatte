@extends('layouts.app')

@section('title', $exercise->name . ' - ' . $exercise->lesson->title_english)

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="flex items-center space-x-4 mb-2">
                <a href="{{ route('lessons.show', $exercise->lesson->id) }}" class="text-blue-500 hover:text-blue-700">← Back to {{ $exercise->lesson->title_english }}</a>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    Exercise
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                    {{ ucfirst($exercise->book_reference) }} p.{{ $exercise->page_number }}
                </span>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                <span class="japanese-text text-4xl mb-2">{{ $exercise->name }}</span>
            </h1>
            
            @if($exercise->overview)
                <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $exercise->overview }}</p>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-3 space-y-6">
        @guest
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">🔒</span>
                        Log In to unlock more functionality
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Creating a Ganbatte account is free and allows you to track your progress and get graded results on exercises and tests.
                    </p>
                </div>
            </div>
        @endguest

        <!-- Exercise Progress Bar -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-2">
                    <span id="quiz-progress-text" class="text-sm text-gray-600 dark:text-gray-400">Question 1 of {{ count($questions) }}</span>
                    <span id="quiz-timer" class="text-sm font-medium text-gray-900 dark:text-gray-100">00:00</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div id="quiz-progress-bar" class="bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <!-- Question Display Area -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-8">
                <div id="question-container">
                    <!-- Questions will be displayed here by JavaScript -->
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between mt-8">
                    <button id="quiz-prev-btn" onclick="quizEngine.previousQuestion()" 
                            class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        ← Previous
                    </button>
                    
                    <button id="quiz-skip-btn" onclick="quizEngine.skipQuestion()" 
                            class="px-6 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors">
                        Skip
                    </button>
                    
                    <button id="quiz-next-btn" onclick="quizEngine.nextQuestion()" 
                            class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        Next →
                    </button>
                    
                    <button id="quiz-submit-btn" onclick="submitExercise()" style="display: none;"
                            class="px-8 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors font-bold">
                        Submit Exercise
                    </button>
                </div>
            </div>
        </div>

        <!-- Results Display (Initially Hidden) -->
        <div id="results-container" style="display: none;" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-8">
                <div class="text-center mb-8">
                    <div id="score-display" class="text-6xl font-bold mb-4"></div>
                    <div id="score-message" class="text-xl text-gray-600 dark:text-gray-400 mb-2"></div>
                    <div id="time-taken" class="text-sm text-gray-500 dark:text-gray-500"></div>
                </div>

                <div id="detailed-results" class="space-y-4 mb-8">
                    <!-- Results will be populated here -->
                </div>

                <div class="flex justify-center space-x-4">
                    <button onclick="retryExercise()" 
                           class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        Try Again
                    </button>
                    <a href="{{ route('lessons.show', $exercise->lesson->id) }}" 
                       class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Back to Lesson
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Exercise Info -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Exercise Details</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Type</span>
                        <span class="font-semibold dark:text-white">Exercise</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Questions</span>
                        <span class="font-semibold dark:text-white">{{ count($questions) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Page</span>
                        <span class="font-semibold dark:text-white">{{ $exercise->page_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Book</span>
                        <span class="font-semibold dark:text-white">{{ ucfirst($exercise->book_reference) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question Types Summary -->
        @php
            $questionTypes = $questions->groupBy('type')->map->count();
        @endphp
        @if($questionTypes->count() > 0)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Question Types</h3>
                <div class="space-y-2">
                    @foreach($questionTypes as $type => $count)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                            <span class="text-sm font-semibold dark:text-white">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Navigation -->
        @php
            // Build a flat ordered list of all content items from pages
            $allContent = collect();
            foreach ($exercise->lesson->pages->sortBy('page_number') as $page) {
                foreach ($page->content as $contentItem) {
                    $allContent->push((object) [
                        'type' => $contentItem->type,
                        'id' => $contentItem->id,
                        'content' => $contentItem->content,
                        'page_number' => $page->page_number,
                    ]);
                }
            }
            
            // Find current item and adjacent items
            $currentIndex = $allContent->search(function($item) use ($exercise) {
                return $item->type === 'exercise' && $item->id === $exercise->id;
            });
            
            $prevItem = $currentIndex !== false && $currentIndex > 0 ? $allContent[$currentIndex - 1] : null;
            $nextItem = $currentIndex !== false && $currentIndex < $allContent->count() - 1 ? $allContent[$currentIndex + 1] : null;
        @endphp
        @if($prevItem || $nextItem)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Navigate</h3>
                    <div class="space-y-3">
                        @if($prevItem)
                            @php
                                $prevUrl = $prevItem->type === 'section' 
                                    ? route('sections.show', $prevItem->id)
                                    : route('exercises.show', $prevItem->id);
                                $prevName = $prevItem->content->name ?? $prevItem->content->title ?? 'Previous Item';
                                $prevType = ucfirst(str_replace('_', ' ', $prevItem->type));
                            @endphp
                            <a href="{{ $prevUrl }}" class="flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                <span class="mr-2">←</span>
                                <div>
                                    <div class="text-sm font-medium">Previous {{ $prevType }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">{{ $prevName }} (p.{{ $prevItem->page_number }})</div>
                                </div>
                            </a>
                        @endif
                        @if($nextItem)
                            @php
                                $nextUrl = $nextItem->type === 'section' 
                                    ? route('sections.show', $nextItem->id)
                                    : route('exercises.show', $nextItem->id);
                                $nextName = $nextItem->content->name ?? $nextItem->content->title ?? 'Next Item';
                                $nextType = ucfirst(str_replace('_', ' ', $nextItem->type));
                            @endphp
                            <a href="{{ $nextUrl }}" class="flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                <div class="flex-1">
                                    <div class="text-sm font-medium">Next {{ $nextType }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">{{ $nextName }} (p.{{ $nextItem->page_number }})</div>
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

<style>
    .japanese-text {
        font-family: 'Noto Sans JP', 'Hiragino Sans', sans-serif;
    }
</style>

<script src="{{ asset('js/quiz-engine.js') }}"></script>
<script>
// Initialize quiz engine with questions data
const questions = @json($questions);
let quizEngine;

// Global function for audio speed control in listening questions
function setAudioSpeed(playerId, speed, buttonElement) {
    const audio = document.getElementById(playerId);
    if (audio) {
        // Set playback rate
        const setRate = () => {
            audio.playbackRate = speed;
        };
        
        // If audio is loaded, set immediately
        if (audio.readyState >= 1) {
            setRate();
        } else {
            // Wait for metadata to load
            audio.addEventListener('loadedmetadata', setRate);
            audio.addEventListener('canplay', setRate);
        }
        
        // Update button styling
        const controlGroup = buttonElement.closest('[data-audio-id]');
        if (controlGroup) {
            controlGroup.querySelectorAll('.speed-btn').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white', 'active');
                btn.classList.add('bg-blue-100', 'text-blue-800');
            });
            
            buttonElement.classList.remove('bg-blue-100', 'text-blue-800');
            buttonElement.classList.add('bg-blue-600', 'text-white', 'active');
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the quiz engine
    quizEngine = new QuizEngine({
        questions: questions,
        mode: 'practice',
        showTimer: true,
        showImmediateFeedback: true,
        allowNavigation: true,
        autoSave: true,
        onQuestionChange: function(index, question) {
            // Custom handling when question changes
            console.log('Question changed to:', index);
        },
        onAnswer: function(question, answer) {
            // Custom handling when answer is provided
            console.log('Answer provided:', answer);
        },
        onComplete: function(results) {
            // Display results
            displayResults(results);
        }
    });
    
    // Start the quiz
    quizEngine.init();
});

// Submit exercise
function submitExercise() {
    const results = quizEngine.submit();
    // If user is authenticated, we could save results to the server here
    @auth
        // saveResultsToServer(results);
    @endauth
}

// Display results
function displayResults(results) {
    // Hide question container, show results
    document.querySelector('.bg-white.dark\\:bg-gray-800:has(#question-container)').style.display = 'none';
    document.getElementById('results-container').style.display = 'block';
    
    // Display score
    const scoreDisplay = document.getElementById('score-display');
    scoreDisplay.textContent = `${results.correct} / ${results.total}`;
    scoreDisplay.className = 'text-6xl font-bold mb-4 ' + 
        (results.percentage >= 80 ? 'text-green-500' : 
         results.percentage >= 60 ? 'text-yellow-500' : 'text-red-500');
    
    // Score message
    const messages = {
        100: '🎉 Perfect! Absolutely amazing!',
        90: '🌟 Excellent work! Nearly perfect!',
        80: '✨ Great job! You\'re doing well!',
        70: '👍 Good effort! Keep practicing!',
        60: '💪 Not bad! Room for improvement.',
        50: '📚 Keep studying! You\'ll get there!',
        0: '🌱 Don\'t give up! Practice makes perfect!'
    };
    
    let message = messages[0];
    const thresholds = Object.keys(messages).map(Number).sort((a, b) => b - a);
    for (let threshold of thresholds) {
        if (results.percentage >= threshold) {
            message = messages[threshold];
            break;
        }
    }
    
    document.getElementById('score-message').textContent = message;
    
    // Time taken
    const minutes = Math.floor(results.timeElapsed / 60);
    const seconds = results.timeElapsed % 60;
    document.getElementById('time-taken').textContent = 
        `Time taken: ${minutes}:${seconds.toString().padStart(2, '0')}`;
    
    // Detailed results
    const resultsDiv = document.getElementById('detailed-results');
    resultsDiv.innerHTML = '<h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Review Your Answers</h3>';
    
    results.details.forEach((detail, index) => {
        const resultCard = document.createElement('div');
        resultCard.className = 'p-4 border-2 rounded-lg ' + 
            (detail.isCorrect ? 
             'border-green-400 bg-green-50 dark:bg-green-900/20 dark:border-green-600' : 
             'border-red-400 bg-red-50 dark:bg-red-900/20 dark:border-red-600');
        
        const questionText = detail.question.question_english || detail.question.question_japanese || '';
        const correctAnswer = Array.isArray(detail.question.correct_answer) ? 
            detail.question.correct_answer[0] : detail.question.correct_answer;
        
        // Format user answer for display
        let userAnswerDisplay = detail.userAnswer;
        if (detail.question.type === 'multiple_choice' && detail.question.options) {
            userAnswerDisplay = detail.question.options[detail.userAnswer] || '(No answer)';
        }
        
        resultCard.innerHTML = `
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Question ${index + 1}</span>
                    ${detail.isCorrect ? 
                      '<span class="ml-2 text-green-600 dark:text-green-400">✓ Correct</span>' : 
                      '<span class="ml-2 text-red-600 dark:text-red-400">✗ Incorrect</span>'}
                </div>
            </div>
            <div class="space-y-2">
                <div><strong class="text-gray-700 dark:text-gray-300">Question:</strong> 
                    <span class="text-gray-900 dark:text-gray-100">${questionText}</span>
                </div>
                <div><strong class="text-gray-700 dark:text-gray-300">Your Answer:</strong> 
                    <span class="${!detail.isCorrect ? 'line-through text-gray-500' : ''} text-gray-900 dark:text-gray-100">
                        ${userAnswerDisplay || '(No answer)'}
                    </span>
                </div>
                ${!detail.isCorrect ? 
                  `<div><strong class="text-gray-700 dark:text-gray-300">Correct Answer:</strong> 
                    <span class="text-green-600 dark:text-green-400 font-medium">
                        ${detail.question.type === 'multiple_choice' ? detail.question.options[correctAnswer] : correctAnswer}
                    </span>
                  </div>` : ''}
                ${detail.question.explanation_english ? 
                  `<div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                    <strong class="text-gray-700 dark:text-gray-300">Explanation:</strong> 
                    <span class="text-gray-600 dark:text-gray-400">${detail.question.explanation_english}</span>
                  </div>` : ''}
            </div>
        `;
        
        resultsDiv.appendChild(resultCard);
    });
}

// Retry exercise
function retryExercise() {
    // Reset and restart
    document.getElementById('results-container').style.display = 'none';
    document.querySelector('.bg-white.dark\\:bg-gray-800:has(#question-container)').style.display = 'block';
    
    // Reinitialize quiz engine
    quizEngine = new QuizEngine({
        questions: questions,
        mode: 'practice',
        showTimer: true,
        showImmediateFeedback: true,
        allowNavigation: true,
        autoSave: true,
        onComplete: function(results) {
            displayResults(results);
        }
    });
    
    quizEngine.init();
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (document.getElementById('results-container').style.display !== 'none') {
        return; // Don't handle shortcuts on results page
    }
    
    if (e.key === 'Enter') {
        const inputField = document.getElementById('answer-input');
        if (inputField && document.activeElement === inputField) {
            // If user is typing in an input field, Enter should move to next question
            if (quizEngine.currentQuestionIndex < questions.length - 1) {
                quizEngine.nextQuestion();
            } else {
                submitExercise();
            }
        }
    } else if (e.key === 'ArrowLeft' && quizEngine.currentQuestionIndex > 0) {
        quizEngine.previousQuestion();
    } else if (e.key === 'ArrowRight' && quizEngine.currentQuestionIndex < questions.length - 1) {
        quizEngine.nextQuestion();
    }
});
</script>

@endsection
