/**
 * Reusable Quiz/Exercise Engine
 * Handles different question types and user interactions
 */

class QuizEngine {
    constructor(config = {}) {
        this.questions = config.questions || [];
        this.currentQuestionIndex = 0;
        this.userAnswers = {};
        this.startTime = Date.now();
        this.mode = config.mode || 'practice'; // 'practice' or 'test'
        this.showImmediateFeedback = config.showImmediateFeedback !== false;
        this.allowNavigation = config.allowNavigation !== false;
        this.autoSave = config.autoSave !== false;
        
        // Callbacks
        this.onQuestionChange = config.onQuestionChange || (() => {});
        this.onComplete = config.onComplete || (() => {});
        this.onAnswer = config.onAnswer || (() => {});
        
        // Timer
        this.timerInterval = null;
        if (config.showTimer) {
            this.startTimer();
        }
    }
    
    /**
     * Initialize the quiz
     */
    init() {
        this.displayQuestion(0);
        this.updateProgress();
        this.updateNavigationButtons();
    }
    
    /**
     * Start the timer
     */
    startTimer() {
        const timerElement = document.getElementById('quiz-timer');
        if (!timerElement) return;
        
        this.timerInterval = setInterval(() => {
            const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            timerElement.textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }, 1000);
    }
    
    /**
     * Stop the timer
     */
    stopTimer() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
        }
    }
    
    /**
     * Display a question
     */
    displayQuestion(index) {
        if (index < 0 || index >= this.questions.length) return;
        
        const question = this.questions[index];
        this.currentQuestionIndex = index;
        
        // Clear previous question display
        const container = document.getElementById('question-container');
        if (!container) return;
        
        container.innerHTML = '';
        
        // Create question display based on type
        switch (question.type) {
            case 'multiple_choice':
                this.displayMultipleChoice(question, container);
                break;
            case 'fill_blank':
                this.displayFillBlank(question, container);
                break;
            case 'translation_j_to_e':
            case 'translation_e_to_j':
                this.displayTranslation(question, container);
                break;
            case 'listening':
                this.displayListening(question, container);
                break;
            case 'sentence_ordering':
                this.displaySentenceOrdering(question, container);
                break;
            default:
                this.displayGenericQuestion(question, container);
        }
        
        // Update UI
        this.updateProgress();
        this.updateNavigationButtons();
        this.onQuestionChange(index, question);
    }
    
    /**
     * Display multiple choice question
     */
    displayMultipleChoice(question, container) {
        // Question text
        const questionDiv = document.createElement('div');
        questionDiv.className = 'mb-6';
        questionDiv.innerHTML = `
            <div class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                ${this.formatQuestionText(question)}
            </div>
            ${question.context ? `<div class="text-sm text-gray-600 dark:text-gray-400">${question.context}</div>` : ''}
        `;
        container.appendChild(questionDiv);
        
        // Options
        const optionsDiv = document.createElement('div');
        optionsDiv.className = 'space-y-3';
        
        question.options.forEach((option, index) => {
            const button = document.createElement('button');
            button.className = 'w-full p-4 text-left border-2 border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800';
            button.setAttribute('data-option-index', index);
            
            // Format option text
            const optionText = typeof option === 'object' ? 
                (option.japanese || option.english || option.text) : option;
            
            button.innerHTML = `
                <div class="flex items-start">
                    <span class="font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-full w-6 h-6 flex items-center justify-center text-sm mr-3">
                        ${String.fromCharCode(65 + index)}
                    </span>
                    <div class="flex-1">
                        ${this.formatText(optionText)}
                    </div>
                </div>
            `;
            
            button.onclick = () => this.selectMultipleChoiceOption(question, index, button);
            
            // Check if this was previously selected
            if (this.userAnswers[question.id] === index) {
                this.markOptionSelected(button);
            }
            
            optionsDiv.appendChild(button);
        });
        
        container.appendChild(optionsDiv);
    }
    
    /**
     * Display fill in the blank question
     */
    displayFillBlank(question, container) {
        // Question text
        const questionDiv = document.createElement('div');
        questionDiv.className = 'mb-6';
        questionDiv.innerHTML = `
            <div class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                ${this.formatQuestionText(question)}
            </div>
            ${question.context ? `<div class="text-sm text-gray-600 dark:text-gray-400">${question.context}</div>` : ''}
        `;
        container.appendChild(questionDiv);
        
        // Input field
        const inputDiv = document.createElement('div');
        inputDiv.className = 'max-w-lg';
        
        const input = document.createElement('input');
        input.type = 'text';
        input.id = 'answer-input';
        input.className = 'w-full px-4 py-3 text-lg border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:border-blue-500 dark:focus:border-blue-400 dark:bg-gray-700 dark:text-gray-100';
        input.placeholder = 'Type your answer...';
        input.value = this.userAnswers[question.id] || '';
        
        // Check if Japanese input is expected
        if (question.type === 'translation_e_to_j' || 
            (question.correct_answer && this.containsJapanese(question.correct_answer[0]))) {
            input.setAttribute('lang', 'ja');
            
            const hint = document.createElement('p');
            hint.className = 'mt-2 text-sm text-gray-600 dark:text-gray-400';
            hint.textContent = 'Tip: Use hiragana, katakana, or kanji as appropriate.';
            inputDiv.appendChild(input);
            inputDiv.appendChild(hint);
        } else {
            inputDiv.appendChild(input);
        }
        
        input.oninput = () => {
            this.userAnswers[question.id] = input.value;
            if (this.autoSave) {
                this.saveProgress();
            }
        };
        
        container.appendChild(inputDiv);
        
        // Focus the input
        setTimeout(() => input.focus(), 100);
    }
    
    /**
     * Display translation question
     */
    displayTranslation(question, container) {
        // Similar to fill blank but with specific formatting
        this.displayFillBlank(question, container);
    }
    
    /**
     * Display listening question
     */
    displayListening(question, container) {
        // Question text
        const questionDiv = document.createElement('div');
        questionDiv.className = 'mb-6';
        questionDiv.innerHTML = `
            <div class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                ${this.formatQuestionText(question)}
            </div>
            ${question.context ? `<div class="text-sm text-gray-600 dark:text-gray-400">${question.context}</div>` : ''}
        `;
        container.appendChild(questionDiv);
        
        // Audio player - check for both audio_filename and audio.filename structures
        const audioFile = question.audio_filename || (question.audio && question.audio.filename);
        const audioDuration = question.audio_duration || (question.audio && question.audio.duration_seconds);
        
        if (audioFile) {
            const playerId = 'audio-player-' + Math.random().toString(36).substr(2, 9);
            const audioDiv = document.createElement('div');
            audioDiv.className = 'mb-6';
            audioDiv.innerHTML = `
                <div class="audio-player bg-gray-50 border border-gray-200 rounded-lg p-4 dark:bg-gray-700 dark:border-gray-700">
                    <div class="space-y-3">
                        <audio 
                            id="${playerId}" 
                            controls 
                            class="w-full h-12"
                        >
                            <source src="/mp3/${audioFile}" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                        
                        <div class="flex items-center justify-center space-x-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Speed:</span>
                            <div class="flex space-x-1" data-audio-id="${playerId}">
                                <button 
                                    class="speed-btn px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                                    data-speed="0.50"
                                    onclick="setAudioSpeed('${playerId}', 0.5, this)"
                                >
                                    0.5x
                                </button>
                                <button 
                                    class="speed-btn px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                                    data-speed="0.75"
                                    onclick="setAudioSpeed('${playerId}', 0.75, this)"
                                >
                                    0.75x
                                </button>
                                <button 
                                    class="speed-btn px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors active"
                                    data-speed="1.0"
                                    onclick="setAudioSpeed('${playerId}', 1.0, this)"
                                >
                                    1.0x
                                </button>
                            </div>
                        </div>
                        
                        ${audioDuration ? 
                            `<div class="text-center text-xs text-gray-500 dark:text-gray-500">
                                Duration: ${Math.floor(audioDuration / 60)}:${(audioDuration % 60).toString().padStart(2, '0')}
                            </div>` : ''}
                    </div>
                </div>
            `;
            container.appendChild(audioDiv);
        }
        
        // Answer input (could be multiple choice or text)
        if (question.options) {
            // For listening with multiple choice, display options only
            const optionsDiv = document.createElement('div');
            optionsDiv.className = 'space-y-3';
            
            question.options.forEach((option, index) => {
                const button = document.createElement('button');
                button.className = 'w-full p-4 text-left border-2 border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800';
                button.setAttribute('data-option-index', index);
                
                const optionText = typeof option === 'object' ? 
                    (option.japanese || option.english || option.text) : option;
                
                button.innerHTML = `
                    <div class="flex items-start">
                        <span class="font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-full w-6 h-6 flex items-center justify-center text-sm mr-3">
                            ${String.fromCharCode(65 + index)}
                        </span>
                        <div class="flex-1">
                            ${this.formatText(optionText)}
                        </div>
                    </div>
                `;
                
                button.onclick = () => this.selectMultipleChoiceOption(question, index, button);
                
                // Check if this was previously selected
                if (this.userAnswers[question.id] === index) {
                    this.markOptionSelected(button);
                }
                
                optionsDiv.appendChild(button);
            });
            
            container.appendChild(optionsDiv);
        } else {
            // For listening with text input
            const inputDiv = document.createElement('div');
            inputDiv.className = 'max-w-lg';
            
            const input = document.createElement('input');
            input.type = 'text';
            input.id = 'answer-input';
            input.className = 'w-full px-4 py-3 text-lg border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:border-blue-500 dark:focus:border-blue-400 dark:bg-gray-700 dark:text-gray-100';
            input.placeholder = 'Type what you hear...';
            input.value = this.userAnswers[question.id] || '';
            
            // For listening questions, we expect Japanese input
            input.setAttribute('lang', 'ja');
            
            const hint = document.createElement('p');
            hint.className = 'mt-2 text-sm text-gray-600 dark:text-gray-400';
            hint.textContent = 'Listen carefully and type what you hear. Use hiragana, katakana, or kanji as appropriate.';
            
            inputDiv.appendChild(input);
            inputDiv.appendChild(hint);
            
            input.oninput = () => {
                this.userAnswers[question.id] = input.value;
                if (this.autoSave) {
                    this.saveProgress();
                }
            };
            
            container.appendChild(inputDiv);
            
            // Focus the input
            setTimeout(() => input.focus(), 100);
        }
    }
    
    /**
     * Display sentence ordering question
     */
    displaySentenceOrdering(question, container) {
        // Question text
        const questionDiv = document.createElement('div');
        questionDiv.className = 'mb-6';
        questionDiv.innerHTML = `
            <div class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                ${this.formatQuestionText(question)}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Drag and drop the words/phrases to form the correct sentence.
            </div>
        `;
        container.appendChild(questionDiv);
        
        // Draggable items
        const itemsDiv = document.createElement('div');
        itemsDiv.className = 'mb-6';
        itemsDiv.innerHTML = '<div class="flex flex-wrap gap-2" id="word-bank"></div>';
        
        const dropZone = document.createElement('div');
        dropZone.className = 'min-h-[60px] p-4 border-2 border-dashed border-gray-400 dark:border-gray-600 rounded-lg mb-4';
        dropZone.id = 'drop-zone';
        dropZone.innerHTML = '<div class="text-gray-500 dark:text-gray-500">Drop words here...</div>';
        
        container.appendChild(dropZone);
        container.appendChild(itemsDiv);
        
        // Initialize drag and drop
        this.initializeDragAndDrop(question);
    }
    
    /**
     * Display generic question
     */
    displayGenericQuestion(question, container) {
        // Fallback for any question type
        this.displayFillBlank(question, container);
    }
    
    /**
     * Select a multiple choice option
     */
    selectMultipleChoiceOption(question, optionIndex, buttonElement) {
        // Remove previous selection
        const container = buttonElement.parentElement;
        container.querySelectorAll('button').forEach(btn => {
            btn.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
            btn.classList.add('border-gray-300', 'dark:border-gray-600');
        });
        
        // Mark as selected
        this.markOptionSelected(buttonElement);
        
        // Store answer
        this.userAnswers[question.id] = optionIndex;
        this.onAnswer(question, optionIndex);
        
        // Show immediate feedback if enabled
        if (this.showImmediateFeedback && this.mode === 'practice') {
            this.showFeedback(question, optionIndex);
        }
        
        // Auto-save if enabled
        if (this.autoSave) {
            this.saveProgress();
        }
    }
    
    /**
     * Mark option as selected
     */
    markOptionSelected(buttonElement) {
        buttonElement.classList.remove('border-gray-300', 'dark:border-gray-600');
        buttonElement.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
    }
    
    /**
     * Show feedback for an answer
     */
    showFeedback(question, answer) {
        const isCorrect = this.checkAnswer(question, answer);
        const feedbackDiv = document.createElement('div');
        feedbackDiv.className = `mt-4 p-4 rounded-lg ${
            isCorrect ? 
            'bg-green-100 dark:bg-green-900/20 border border-green-400 dark:border-green-600' : 
            'bg-red-100 dark:bg-red-900/20 border border-red-400 dark:border-red-600'
        }`;
        
        feedbackDiv.innerHTML = `
            <div class="flex items-start">
                <span class="${isCorrect ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'} mr-2">
                    ${isCorrect ? '✓' : '✗'}
                </span>
                <div>
                    <div class="font-medium ${isCorrect ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'}">
                        ${isCorrect ? 'Correct!' : 'Incorrect'}
                    </div>
                    ${question.explanation ? 
                        `<div class="mt-1 text-sm ${isCorrect ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'}">
                            ${this.formatExplanation(question)}
                        </div>` : ''}
                </div>
            </div>
        `;
        
        document.getElementById('question-container').appendChild(feedbackDiv);
    }
    
    /**
     * Check if an answer is correct
     */
    checkAnswer(question, answer) {
        if (Array.isArray(question.correct_answer)) {
            return question.correct_answer.includes(answer);
        }
        return question.correct_answer === answer;
    }
    
    /**
     * Navigate to next question
     */
    nextQuestion() {
        if (this.currentQuestionIndex < this.questions.length - 1) {
            this.displayQuestion(this.currentQuestionIndex + 1);
        }
    }
    
    /**
     * Navigate to previous question
     */
    previousQuestion() {
        if (this.currentQuestionIndex > 0) {
            this.displayQuestion(this.currentQuestionIndex - 1);
        }
    }
    
    /**
     * Skip current question
     */
    skipQuestion() {
        this.userAnswers[this.questions[this.currentQuestionIndex].id] = null;
        this.nextQuestion();
    }
    
    /**
     * Submit all answers
     */
    submit() {
        this.stopTimer();
        const results = this.calculateResults();
        this.onComplete(results);
        return results;
    }
    
    /**
     * Calculate results
     */
    calculateResults() {
        let correct = 0;
        let total = this.questions.length;
        const details = [];
        
        this.questions.forEach(question => {
            const userAnswer = this.userAnswers[question.id];
            const isCorrect = this.checkAnswer(question, userAnswer);
            if (isCorrect) correct++;
            
            details.push({
                question: question,
                userAnswer: userAnswer,
                isCorrect: isCorrect
            });
        });
        
        return {
            correct: correct,
            total: total,
            percentage: Math.round((correct / total) * 100),
            timeElapsed: Math.floor((Date.now() - this.startTime) / 1000),
            details: details
        };
    }
    
    /**
     * Update progress bar
     */
    updateProgress() {
        const progressBar = document.getElementById('quiz-progress-bar');
        const progressText = document.getElementById('quiz-progress-text');
        
        if (progressBar) {
            const percentage = ((this.currentQuestionIndex + 1) / this.questions.length) * 100;
            progressBar.style.width = percentage + '%';
        }
        
        if (progressText) {
            progressText.textContent = `Question ${this.currentQuestionIndex + 1} of ${this.questions.length}`;
        }
    }
    
    /**
     * Update navigation buttons
     */
    updateNavigationButtons() {
        const prevBtn = document.getElementById('quiz-prev-btn');
        const nextBtn = document.getElementById('quiz-next-btn');
        const submitBtn = document.getElementById('quiz-submit-btn');
        
        if (prevBtn) {
            prevBtn.disabled = this.currentQuestionIndex === 0;
        }
        
        if (nextBtn) {
            nextBtn.style.display = this.currentQuestionIndex < this.questions.length - 1 ? 'inline-block' : 'none';
        }
        
        if (submitBtn) {
            submitBtn.style.display = this.currentQuestionIndex === this.questions.length - 1 ? 'inline-block' : 'none';
        }
    }
    
    /**
     * Save progress (for auto-save feature)
     */
    saveProgress() {
        if (!this.autoSave) return;
        
        const data = {
            currentIndex: this.currentQuestionIndex,
            answers: this.userAnswers,
            timeElapsed: Math.floor((Date.now() - this.startTime) / 1000)
        };
        
        localStorage.setItem(`quiz-progress-${this.questions[0]?.lesson_id}`, JSON.stringify(data));
    }
    
    /**
     * Load saved progress
     */
    loadProgress() {
        if (!this.autoSave) return null;
        
        const saved = localStorage.getItem(`quiz-progress-${this.questions[0]?.lesson_id}`);
        if (saved) {
            return JSON.parse(saved);
        }
        return null;
    }
    
    /**
     * Format question text with furigana support
     */
    formatQuestionText(question) {
        const text = question.question_japanese || question.question_english || question.question || '';
        return this.formatText(text);
    }
    
    /**
     * Format explanation text
     */
    formatExplanation(question) {
        const text = question.explanation_english || question.explanation_japanese || question.explanation || '';
        return this.formatText(text);
    }
    
    /**
     * Format text with furigana support
     */
    formatText(text) {
        if (!text) return '';
        
        // Convert {漢字|かんじ} format to ruby tags
        return text.replace(/\{([^|]+)\|([^}]+)\}/g, function(match, kanji, furigana) {
            return `<ruby class="furigana">${kanji}<rt>${furigana}</rt></ruby>`;
        });
    }
    
    /**
     * Check if text contains Japanese characters
     */
    containsJapanese(text) {
        return /[\u3000-\u303f\u3040-\u309f\u30a0-\u30ff\u4e00-\u9faf\u3400-\u4dbf]/.test(text);
    }
    
    /**
     * Initialize drag and drop for sentence ordering
     */
    initializeDragAndDrop(question) {
        // Implementation for drag and drop functionality
        // This would be more complex and could use a library like Sortable.js
        console.log('Drag and drop initialization needed for sentence ordering questions');
    }
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = QuizEngine;
}
