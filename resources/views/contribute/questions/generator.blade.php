@extends('layouts.app')

@section('title', 'Question JSON Generator')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <a href="{{ route('contribute.index') }}" class="text-blue-500 hover:text-blue-700 mr-4">
                ← Back to Contribute
            </a>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">Question JSON Generator</h1>
        <p class="text-lg text-gray-600 dark:text-gray-400">
            Generate question JSON files for lessons with proper structure and formatting. 
            Supports fill-blank and multiple-choice question types with optional audio for listening questions.
        </p>
    </div>

    <!-- Question JSON Generator -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
        <div class="p-6">
            <!-- Configuration Section -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Lesson Configuration</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="lesson-id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Lesson ID
                        </label>
                        <input type="text" id="lesson-id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g., 01, 02, 25" maxlength="2">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Will create lesson ID: mnn-lesson-XX</p>
                    </div>
                    <div>
                        <label for="starting-question-number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Starting Question Number
                        </label>
                        <input type="number" id="starting-question-number" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="1" min="1" max="999" value="1">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Question IDs will increment from this number</p>
                    </div>
                </div>
                <div class="mt-4">
                    <button id="add-question" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md font-medium transition-colors">
                        ➕ Add First Question
                    </button>
                </div>
            </div>

            <!-- Dynamic Questions Section -->
            <div id="questions-section" class="space-y-6" style="display: none;">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Questions</h3>
                    <button id="add-another-question" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md font-medium transition-colors">
                        ➕ Add Another Question
                    </button>
                </div>
                <div id="questions-container"></div>
                <div class="flex justify-center space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button id="generate-json" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-md font-medium transition-colors text-lg">
                        🚀 Generate JSON
                    </button>
                    <button id="download-json" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-md font-medium transition-colors text-lg" style="display: none;">
                        💾 Download JSON
                    </button>
                </div>
            </div>

            <!-- Output Section -->
            <div id="output-section" class="mt-8" style="display: none;">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Generated JSON</h3>
                <div class="bg-gray-900 rounded-lg p-4 relative">
                    <button id="copy-json" class="absolute top-2 right-2 bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm transition-colors">
                        Copy
                    </button>
                    <pre id="json-output" class="text-green-400 dark:text-gray-100 text-sm overflow-auto max-h-96 font-mono"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
class QuestionJSONGenerator {
    constructor() {
        this.lessonId = '';
        this.startingQuestionNumber = 1;
        this.questionCount = 0;
        this.questions = [];
        this.bindEvents();
    }

    bindEvents() {
        document.getElementById('add-question').addEventListener('click', () => this.addFirstQuestion());
        document.getElementById('add-another-question').addEventListener('click', () => this.addQuestion());
        document.getElementById('generate-json').addEventListener('click', () => this.generateJSON());
        document.getElementById('copy-json').addEventListener('click', () => this.copyJSON());
        document.getElementById('download-json').addEventListener('click', () => this.downloadJSON());
    }

    addFirstQuestion() {
        const lessonId = document.getElementById('lesson-id').value.trim().padStart(2, '0');
        const startingNumber = parseInt(document.getElementById('starting-question-number').value);

        if (!lessonId) {
            alert('Please enter a lesson ID.');
            return;
        }

        if (!startingNumber || startingNumber < 1) {
            alert('Please enter a valid starting question number.');
            return;
        }

        this.lessonId = lessonId;
        this.startingQuestionNumber = startingNumber;
        
        document.getElementById('questions-section').style.display = 'block';
        this.addQuestion();
    }

    addQuestion() {
        const currentNumber = this.startingQuestionNumber + this.questionCount;
        const questionId = `mnn-${this.lessonId}-q${currentNumber.toString().padStart(3, '0')}`;
        
        const questionForm = this.createQuestionForm(questionId, this.questionCount);
        document.getElementById('questions-container').appendChild(questionForm);
        
        this.questionCount++;
    }

    createQuestionForm(questionId, index) {
        const div = document.createElement('div');
        div.className = 'border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-gray-50 dark:bg-gray-800';
        div.dataset.questionIndex = index;
        div.innerHTML = `
            <div class="mb-4 flex justify-between items-center">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                    <span class="mr-2">❓</span>
                    Question ${index + 1}: ${questionId}
                </h4>
                <button type="button" onclick="this.closest('div[data-question-index]').remove()" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors">
                    🗑️ Remove
                </button>
            </div>
            
            <!-- Question Type -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Question Type *</label>
                <select data-field="type" data-question="${questionId}" class="question-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" onchange="this.closest('div[data-question-index]').querySelector('[data-target-type]').dispatchEvent(new CustomEvent('typeChanged', {detail: {type: this.value, questionId: '${questionId}'}}))">
                    <option value="">Select Type...</option>
                    <option value="fill_blank">Fill in the Blank</option>
                    <option value="multiple_choice">Multiple Choice</option>
                </select>
            </div>

            <!-- Basic Question Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Difficulty *</label>
                    <select data-field="difficulty" data-question="${questionId}" class="question-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select...</option>
                        <option value="beginner" selected>Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Points *</label>
                    <input type="number" data-field="points" data-question="${questionId}" class="question-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="1" min="0" max="10" value="1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tags (comma-separated)</label>
                    <input type="text" data-field="tags" data-question="${questionId}" class="question-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="grammar, vocabulary">
                </div>
            </div>

            <!-- Question Text -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Question (English) *</label>
                    <textarea data-field="question.english" data-question="${questionId}" class="question-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" rows="3" placeholder="Enter the question in English"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Question (Japanese)</label>
                    <textarea data-field="question.japanese" data-question="${questionId}" class="question-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" rows="3" placeholder="Enter the question in Japanese (optional)"></textarea>
                </div>
            </div>

            <!-- Vocabulary IDs -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vocabulary IDs (comma-separated)</label>
                <input type="text" data-field="vocabulary_ids" data-question="${questionId}" class="question-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="mnn-01-001, mnn-01-002">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">List vocabulary IDs that this question tests</p>
            </div>

            <!-- Explanation -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Explanation (English) *</label>
                <textarea data-field="explanation.english" data-question="${questionId}" class="question-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" rows="2" placeholder="Explain why this is the correct answer"></textarea>
            </div>

            <!-- Dynamic Type-Specific Content -->
            <div data-target-type class="type-specific-content" data-question="${questionId}">
                <!-- Content will be populated based on selected type -->
            </div>
        `;
        
        // Add event listener for type changes
        const typeSpecificContent = div.querySelector('[data-target-type]');
        typeSpecificContent.addEventListener('typeChanged', (e) => {
            this.updateTypeSpecificContent(e.detail.type, e.detail.questionId, typeSpecificContent);
        });
        
        return div;
    }

    updateTypeSpecificContent(type, questionId, container) {
        switch(type) {
            case 'fill_blank':
                container.innerHTML = `
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                        <h5 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Fill in the Blank Settings</h5>
                        
                        <!-- Audio Section (Optional) -->
                        <div class="mb-6">
                            <h6 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Audio (Optional for Listening Questions)</h6>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Audio Filename</label>
                                    <input type="text" data-field="audio.filename" data-question="${questionId}" class="question-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="audio_file.mp3">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Correct Answers -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Correct Answers *</label>
                            <textarea data-field="correct_answer" data-question="${questionId}" class="question-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" rows="5" placeholder="Enter each correct answer on a new line:&#10;Answer 1&#10;Answer 2&#10;Answer 3"></textarea>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Each line will be treated as a separate acceptable answer</p>
                        </div>
                    </div>
                `;
                break;
            case 'multiple_choice':
                container.innerHTML = `
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                        <h5 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Multiple Choice Settings</h5>
                        
                        <!-- Audio Section (Optional) -->
                        <div class="mb-6">
                            <h6 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Audio (Optional for Listening Questions)</h6>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Audio Filename</label>
                                    <input type="text" data-field="audio.filename" data-question="${questionId}" class="question-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="audio_file.mp3">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Multiple Choice Options -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Correct Answer (0-based index) *</label>
                            <input type="number" data-field="correct_answer" data-question="${questionId}" class="question-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0" min="0" max="9">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Index of the correct option (0 = first option, 1 = second option, etc.)</p>
                        </div>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <h6 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Answer Options</h6>
                                <button type="button" onclick="this.closest('.type-specific-content').querySelector('[data-add-option]').click()" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors">
                                    ➕ Add Option
                                </button>
                            </div>
                            <div data-options-container="${questionId}" class="space-y-3">
                                <!-- Options will be added here -->
                            </div>
                            <button type="button" data-add-option onclick="generator.addMultipleChoiceOption('${questionId}')" style="display: none;">Add Option</button>
                        </div>
                    </div>
                `;
                // Add initial options
                this.addMultipleChoiceOption(questionId);
                this.addMultipleChoiceOption(questionId);
                break;

            default:
                container.innerHTML = '<p class="text-sm text-gray-500 dark:text-gray-400 italic">Select a question type to see specific options</p>';
        }
    }

    addMultipleChoiceOption(questionId) {
        const container = document.querySelector(`[data-options-container="${questionId}"]`);
        if (!container) return;
        
        const optionIndex = container.children.length;
        const optionDiv = document.createElement('div');
        optionDiv.className = 'border border-gray-300 dark:border-gray-600 rounded p-3 bg-white dark:bg-gray-700';
        optionDiv.innerHTML = `
            <div class="flex justify-between items-center mb-2">
                <h6 class="text-sm font-medium text-gray-900 dark:text-gray-100">Option ${optionIndex + 1}</h6>
                <button type="button" onclick="this.closest('div').remove()" class="text-red-500 hover:text-red-700 text-sm">
                    🗑️ Remove
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">English *</label>
                    <input type="text" data-field="options.${optionIndex}.english" data-question="${questionId}" class="question-input w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" placeholder="Option text in English">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Japanese</label>
                    <input type="text" data-field="options.${optionIndex}.japanese" data-question="${questionId}" class="question-input w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" placeholder="Option text in Japanese">
                </div>
            </div>
            <div class="mt-2">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Explanation</label>
                <textarea data-field="options.${optionIndex}.explanation" data-question="${questionId}" class="question-input w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" rows="2" placeholder="Explain why this option is correct/incorrect"></textarea>
            </div>
            <div class="mt-2">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Furigana</label>
                <input type="text" data-field="options.${optionIndex}.furigana" data-question="${questionId}" class="question-input w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm" placeholder="{漢字|かん|じ}">
            </div>
        `;
        
        container.appendChild(optionDiv);
    }

    generateJSON() {
        const questions = [];
        
        // Get all question forms
        const questionForms = document.querySelectorAll('[data-question-index]');
        
        for (let form of questionForms) {
            const questionData = this.collectQuestionData(form);
            if (questionData) {
                questions.push(questionData);
            }
        }

        if (questions.length === 0) {
            alert('No valid questions found. Please fill in at least the required fields for at least one question.');
            return;
        }

        const jsonData = { questions };
        const jsonString = JSON.stringify(jsonData, null, 2);
        
        document.getElementById('json-output').textContent = jsonString;
        document.getElementById('output-section').style.display = 'block';
        document.getElementById('download-json').style.display = 'inline-block';
        
        // Scroll to output
        document.getElementById('output-section').scrollIntoView({ behavior: 'smooth' });
    }

    collectQuestionData(form) {
        const inputs = form.querySelectorAll('[data-question]');
        if (inputs.length === 0) return null;
        
        const questionId = inputs[0].dataset.question;
        const data = {
            id: questionId,
            lesson_id: `mnn-lesson-${this.lessonId}`,
            question: {}
        };

        let hasRequiredData = false;
        let questionType = null;
        
        // First pass: collect question type
        inputs.forEach(input => {
            const field = input.dataset.field;
            if (field === 'type' && input.value) {
                questionType = input.value.trim();
            }
        });
        
        // Second pass: collect all data
        inputs.forEach(input => {
            const field = input.dataset.field;
            
            // Handle different input types and check for undefined values
            let value = '';
            if (input.type === 'checkbox') {
                // For checkboxes, we only care if they're checked, not their value
                return;
            } else if (input.value !== undefined && input.value !== null) {
                value = input.value.trim();
            }
            
            if (!value && field !== 'question.japanese') return; // Skip empty optional fields
            
            // Handle nested fields
            if (field.includes('.')) {
                const [parent, child] = field.split('.');
                if (!data[parent]) data[parent] = {};
                
                if (parent === 'options') {
                    // Handle multiple choice options
                    const [, optionIndex, optionField] = field.split('.');
                    if (!data.options) data.options = [];
                    if (!data.options[optionIndex]) data.options[optionIndex] = {};
                    if (value) { // Only add non-empty values
                        data.options[optionIndex][optionField] = value;
                    }
                } else {
                    data[parent][child] = value;
                }
            } else if (field === 'type') {
                data[field] = value;
                if (value) hasRequiredData = true;
            } else if (field === 'tags') {
                if (value) {
                    data[field] = value.split(',').map(tag => tag.trim()).filter(tag => tag.length > 0);
                }
            } else if (field === 'vocabulary_ids') {
                if (value) {
                    data[field] = value.split(',').map(id => id.trim()).filter(id => id.length > 0);
                }
            } else if (field === 'points') {
                data[field] = parseInt(value) || 1;
            } else if (field === 'correct_answer') {
                if (questionType === 'multiple_choice') {
                    // For multiple choice, it's a number (index)
                    data[field] = parseInt(value) || 0;
                } else if (questionType === 'fill_blank') {
                    // For fill_blank, split by line breaks into array
                    data[field] = value.split('\n').map(answer => answer.trim()).filter(answer => answer.length > 0);
                } else {
                    // Default case
                    data[field] = value;
                }
                if (value) hasRequiredData = true;
            } else {
                data[field] = value;
                // Check for other required fields
                if (field === 'difficulty' || field === 'question.english' || field === 'explanation.english') {
                    if (value) hasRequiredData = true;
                }
            }
        });

        return hasRequiredData ? data : null;
    }

    copyJSON() {
        const jsonText = document.getElementById('json-output').textContent;
        navigator.clipboard.writeText(jsonText).then(() => {
            const button = document.getElementById('copy-json');
            const originalText = button.textContent;
            button.textContent = 'Copied!';
            button.className = button.className.replace('bg-gray-700 hover:bg-gray-600', 'bg-green-600');
            
            setTimeout(() => {
                button.textContent = originalText;
                button.className = button.className.replace('bg-green-600', 'bg-gray-700 hover:bg-gray-600');
            }, 2000);
        }).catch(() => {
            alert('Failed to copy JSON to clipboard');
        });
    }

    downloadJSON() {
        const jsonText = document.getElementById('json-output').textContent;
        const blob = new Blob([jsonText], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = `lesson-${this.lessonId}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
}

// Initialize the generator and make it globally accessible
let generator;
document.addEventListener('DOMContentLoaded', () => {
    generator = new QuestionJSONGenerator();
});
</script>

@endsection
