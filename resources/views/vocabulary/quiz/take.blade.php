@extends('layouts.app')

@section('title', 'Vocabulary Quiz - In Progress')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Quiz Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Vocabulary Quiz</h1>
                <button onclick="if(confirm('Are you sure you want to quit the quiz?')) window.location.href='{{ route('vocabulary.quiz.index') }}'"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Quiz Info -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Mode:</span>
                    <span class="font-medium text-gray-900 dark:text-gray-100 ml-1">
                        {{ ucfirst($config['mode']) }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Difficulty:</span>
                    <span class="font-medium text-gray-900 dark:text-gray-100 ml-1">
                        {{ ucfirst($config['difficulty']) }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Lessons:</span>
                    <span class="font-medium text-gray-900 dark:text-gray-100 ml-1">
                        {{ $config['lesson_from'] }} - {{ $config['lesson_to'] }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Timer:</span>
                    <span id="timer" class="font-medium text-gray-900 dark:text-gray-100 ml-1">00:00</span>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-4">
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                    <span>Question <span id="current-question">1</span> of {{ $config['total_questions'] }}</span>
                    <span id="progress-percentage">0%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div id="progress-bar" class="bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quiz Content -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-8">
            <div id="quiz-container">
                <!-- Question Display -->
                <div id="question-display" class="mb-8">
                    <div class="text-center">
                        <div id="question-text" class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2 japanese-text"></div>
                        <div id="question-furigana" class="text-lg text-gray-600 dark:text-gray-400 mb-4 japanese-text"></div>
                        <div id="question-instruction" class="text-sm text-gray-500 dark:text-gray-500"></div>
                    </div>
                </div>

                <!-- Answer Input Area -->
                <div id="answer-area" class="mb-6">
                    <!-- Will be populated based on difficulty level -->
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between mt-8">
                    <button id="prev-btn" onclick="previousQuestion()" 
                            class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        ← Previous
                    </button>
                    
                    <button id="skip-btn" onclick="skipQuestion()" 
                            class="px-6 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors">
                        Skip
                    </button>
                    
                    <button id="next-btn" onclick="nextQuestion()" 
                            class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        Next →
                    </button>
                    
                    <button id="submit-btn" onclick="submitQuiz()" style="display: none;"
                            class="px-8 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors font-bold">
                        Submit Quiz
                    </button>
                </div>
            </div>

            <!-- Results Display (Initially Hidden) -->
            <div id="results-container" style="display: none;">
                <div class="text-center mb-8">
                    <div id="score-display" class="text-6xl font-bold mb-4"></div>
                    <div id="score-message" class="text-xl text-gray-600 dark:text-gray-400 mb-2"></div>
                    <div id="time-taken" class="text-sm text-gray-500 dark:text-gray-500"></div>
                </div>

                <div id="detailed-results" class="space-y-4 mb-8">
                    <!-- Will be populated with results -->
                </div>

                <div class="flex justify-center space-x-4">
                    <a href="{{ route('vocabulary.quiz.index') }}" 
                       class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        Take Another Quiz
                    </a>
                    <a href="{{ route('vocabulary.index') }}" 
                       class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Browse Vocabulary
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .japanese-text {
        font-family: 'Noto Sans JP', 'Hiragino Sans', sans-serif;
    }
</style>

<script>
// Quiz state
const questions = @json($questions);
let currentQuestionIndex = 0;
let userAnswers = {};
let startTime = Date.now();
let timerInterval;

// Initialize quiz
document.addEventListener('DOMContentLoaded', function() {
    startTimer();
    displayQuestion(0);
});

// Timer functionality
function startTimer() {
    timerInterval = setInterval(function() {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        document.getElementById('timer').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }, 1000);
}

// Display current question
function displayQuestion(index) {
    const question = questions[index];
    const isRecognition = question.type === 'recognition';
    
    // Update progress
    document.getElementById('current-question').textContent = index + 1;
    document.getElementById('progress-percentage').textContent = 
        Math.round(((index + 1) / questions.length) * 100) + '%';
    document.getElementById('progress-bar').style.width = 
        ((index + 1) / questions.length * 100) + '%';
    
    // Update question display
    if (isRecognition) {
        // Japanese to English
        document.getElementById('question-text').innerHTML = 
            question.question_furigana ? 
            createFuriganaElement(question.question_furigana) : 
            question.question;
        document.getElementById('question-furigana').innerHTML = '';
        document.getElementById('question-instruction').textContent = 
            'What is the English meaning?';
    } else {
        // English to Japanese
        document.getElementById('question-text').textContent = question.question;
        document.getElementById('question-furigana').innerHTML = '';
        document.getElementById('question-instruction').textContent = 
            'What is the Japanese word?';
    }
    
    // Setup answer area based on difficulty
    const answerArea = document.getElementById('answer-area');
    answerArea.innerHTML = '';
    
    if (question.difficulty === 'easy') {
        // Multiple choice
        const optionsDiv = document.createElement('div');
        optionsDiv.className = 'grid grid-cols-1 md:grid-cols-2 gap-4';
        
        question.options.forEach((option, i) => {
            const button = document.createElement('button');
            button.className = 'w-full p-4 text-left border-2 border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors cursor-pointer text-gray-900 dark:text-gray-100';
            button.setAttribute('data-option-btn', 'true');
            
            // For recall mode, options may contain furigana that needs processing
            if (!isRecognition && option.includes('{')) {
                button.innerHTML = createFuriganaElement(option);
            } else {
                button.textContent = option;
            }
            
            button.onclick = function() {
                selectOption(index, option, button);
            };
            
            // Check if this option was previously selected
            if (userAnswers[index] === option) {
                button.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
                button.classList.remove('border-gray-300', 'dark:border-gray-600');
            }
            
            optionsDiv.appendChild(button);
        });
        
        answerArea.appendChild(optionsDiv);
    } else {
        // Text input for hard mode
        const inputDiv = document.createElement('div');
        inputDiv.className = 'max-w-md mx-auto';
        
        const input = document.createElement('input');
        input.type = 'text';
        input.id = 'answer-input';
        input.className = 'w-full px-4 py-3 text-lg border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:border-blue-500 dark:focus:border-blue-400 dark:bg-gray-700 dark:text-gray-100';
        input.placeholder = isRecognition ? 'Type the English meaning...' : 'Type the Japanese word...';
        input.value = userAnswers[index] || '';
        input.onchange = function() {
            userAnswers[index] = this.value;
        };
        
        // Add IME hint for Japanese input
        if (!isRecognition) {
            input.setAttribute('lang', 'ja');
            const hint = document.createElement('p');
            hint.className = 'mt-2 text-sm text-gray-600 dark:text-gray-400';
            hint.textContent = 'Tip: Use hiragana, katakana, or kanji. Both kanji and hiragana readings are accepted.';
            inputDiv.appendChild(input);
            inputDiv.appendChild(hint);
        } else {
            inputDiv.appendChild(input);
        }
        
        answerArea.appendChild(inputDiv);
        
        // Focus the input
        setTimeout(() => input.focus(), 100);
    }
    
    // Update navigation buttons
    document.getElementById('prev-btn').disabled = index === 0;
    document.getElementById('next-btn').style.display = index < questions.length - 1 ? 'inline-block' : 'none';
    document.getElementById('submit-btn').style.display = index === questions.length - 1 ? 'inline-block' : 'none';
    document.getElementById('skip-btn').style.display = index === questions.length - 1 ? 'none' : 'inline-block';
}

// Create furigana ruby elements that respect the toggle
function createFuriganaElement(text) {
    if (!text) return text;
    
    // Check localStorage for furigana state (defaults to enabled if not set)
    const furiganaEnabled = localStorage.getItem('furigana-enabled') !== 'false';
    const classes = furiganaEnabled ? 'furigana' : 'furigana no-furigana';
    
    // Convert {漢字|かん|じ} format to ruby tags with proper classes
    return text.replace(/\{([^|]+)((?:\|[^}]*)+)\}/g, function(match, kanji, furigana) {
        const furiganaParts = furigana.split('|').filter(p => p);
        
        if (furiganaParts.length === 1) {
            return `<ruby class="${classes}">${kanji}<rt>${furiganaParts[0]}</rt></ruby>`;
        } else {
            const kanjiChars = kanji.split('');
            let result = `<ruby class="${classes}">`;
            for (let i = 0; i < kanjiChars.length; i++) {
                result += kanjiChars[i] + '<rt>' + (furiganaParts[i] || '') + '</rt>';
            }
            result += '</ruby>';
            return result;
        }
    });
}

// Handle option selection
function selectOption(questionIndex, answer, buttonElement) {
    // Remove previous selection styling
    document.querySelectorAll('[data-option-btn="true"]').forEach(btn => {
        btn.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        btn.classList.add('border-gray-300', 'dark:border-gray-600');
    });
    
    // Mark as selected
    buttonElement.classList.remove('border-gray-300', 'dark:border-gray-600');
    buttonElement.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
    
    // Store answer
    userAnswers[questionIndex] = answer;
}

// Navigation functions
function previousQuestion() {
    if (currentQuestionIndex > 0) {
        // Save current answer if it's a text input
        const input = document.getElementById('answer-input');
        if (input) {
            userAnswers[currentQuestionIndex] = input.value;
        }
        
        currentQuestionIndex--;
        displayQuestion(currentQuestionIndex);
    }
}

function nextQuestion() {
    if (currentQuestionIndex < questions.length - 1) {
        // Save current answer if it's a text input
        const input = document.getElementById('answer-input');
        if (input) {
            userAnswers[currentQuestionIndex] = input.value;
        }
        
        currentQuestionIndex++;
        displayQuestion(currentQuestionIndex);
    }
}

function skipQuestion() {
    // Clear current answer
    userAnswers[currentQuestionIndex] = '';
    
    if (currentQuestionIndex < questions.length - 1) {
        nextQuestion();
    }
}

// Submit quiz
function submitQuiz() {
    // Save final answer if it's a text input
    const input = document.getElementById('answer-input');
    if (input) {
        userAnswers[currentQuestionIndex] = input.value;
    }
    
    // Stop timer
    clearInterval(timerInterval);
    
    // Prepare answers array (maintain order)
    const answersArray = [];
    for (let i = 0; i < questions.length; i++) {
        answersArray[i] = userAnswers[i] || '';
    }
    
    // Submit to server
    fetch('{{ route('vocabulary.quiz.submit') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ answers: answersArray })
    })
    .then(response => response.json())
    .then(data => {
        displayResults(data);
    })
    .catch(error => {
        console.error('Error submitting quiz:', error);
        alert('Error submitting quiz. Please try again.');
    });
}

// Display results
function displayResults(data) {
    // Hide quiz container, show results
    document.getElementById('quiz-container').style.display = 'none';
    document.getElementById('results-container').style.display = 'block';
    
    // Display score
    const scoreDisplay = document.getElementById('score-display');
    scoreDisplay.textContent = `${data.score} / ${data.total}`;
    scoreDisplay.className = 'text-6xl font-bold mb-4 ' + 
        (data.percentage >= 80 ? 'text-green-500' : 
         data.percentage >= 60 ? 'text-yellow-500' : 'text-red-500');
    
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
    
    // Check thresholds from highest to lowest
    let message = messages[0]; // Default message
    const thresholds = Object.keys(messages).map(Number).sort((a, b) => b - a);
    for (let threshold of thresholds) {
        if (data.percentage >= threshold) {
            message = messages[threshold];
            break;
        }
    }
    
    document.getElementById('score-message').textContent = message;
    
    // Format time taken properly (ensure positive values)
    const totalSeconds = Math.max(0, Math.floor(data.time_taken));
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    document.getElementById('time-taken').textContent = 
        `Time taken: ${minutes}:${seconds.toString().padStart(2, '0')}`;
    
    // Display detailed results
    const resultsDiv = document.getElementById('detailed-results');
    resultsDiv.innerHTML = '<h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Review Your Answers</h3>';
    
    data.results.forEach((result, index) => {
        const question = questions[index];
        const resultCard = document.createElement('div');
        resultCard.className = 'p-4 border-2 rounded-lg ' + 
            (result.is_correct ? 
             'border-green-400 bg-green-50 dark:bg-green-900/20 dark:border-green-600' : 
             'border-red-400 bg-red-50 dark:bg-red-900/20 dark:border-red-600');
        
        let questionDisplay = result.question;
        if (question.question_furigana && question.type === 'recognition') {
            questionDisplay = createFuriganaElement(question.question_furigana);
        }
        
        resultCard.innerHTML = `
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Question ${index + 1}</span>
                    ${result.is_correct ? 
                      '<span class="ml-2 text-green-600 dark:text-green-400">✓ Correct</span>' : 
                      '<span class="ml-2 text-red-600 dark:text-red-400">✗ Incorrect</span>'}
                </div>
            </div>
            <div class="space-y-2">
                <div><strong class="text-gray-700 dark:text-gray-300">Question:</strong> 
                    <span class="japanese-text text-gray-900 dark:text-gray-100">${questionDisplay}</span>
                </div>
                <div><strong class="text-gray-700 dark:text-gray-300">Your Answer:</strong> 
                    <span class="${!result.is_correct ? 'line-through text-gray-500' : ''} text-gray-900 dark:text-gray-100 japanese-text">${
                        result.user_answer && result.user_answer.includes('{') ? 
                        createFuriganaElement(result.user_answer) : 
                        (result.user_answer || '(No answer)')
                    }</span>
                </div>
                ${!result.is_correct ? 
                  `<div><strong class="text-gray-700 dark:text-gray-300">Correct Answer:</strong> 
                    <span class="text-green-600 dark:text-green-400 font-medium japanese-text">${
                        result.display_answer.includes('{') ? createFuriganaElement(result.display_answer) : result.display_answer
                    }</span>
                  </div>` : ''}
            </div>
        `;
        
        resultsDiv.appendChild(resultCard);
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (document.getElementById('results-container').style.display !== 'none') {
        return; // Don't handle shortcuts on results page
    }
    
    if (e.key === 'Enter') {
        if (currentQuestionIndex < questions.length - 1) {
            nextQuestion();
        } else {
            submitQuiz();
        }
    } else if (e.key === 'ArrowLeft' && currentQuestionIndex > 0) {
        previousQuestion();
    } else if (e.key === 'ArrowRight' && currentQuestionIndex < questions.length - 1) {
        nextQuestion();
    }
});
</script>
@endsection
