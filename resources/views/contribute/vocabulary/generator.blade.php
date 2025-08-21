@extends('layouts.app')

@section('title', 'Vocabulary JSON Generator')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <a href="{{ route('contribute.index') }}" class="text-blue-500 hover:text-blue-700 mr-4">
                ← Back to Contribute
            </a>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">Vocabulary JSON Generator</h1>
        <p class="text-lg text-gray-600 dark:text-gray-400">
            Generate vocabulary lesson JSON files with proper structure and formatting. 
            Enter lesson details and vocabulary range to create input forms. 
            Do not attempt to use this generator without being trained on usage and receiving the necessary files.
        </p>
    </div>

    <!-- Vocabulary JSON Generator -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
        <div class="p-6">
            <!-- Configuration Section -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Lesson Configuration</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="lesson-id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Lesson ID
                        </label>
                        <input type="text" id="lesson-id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g., 01, 02, 25" maxlength="2">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Will create lesson ID: mnn-lesson-XX</p>
                    </div>
                    <div>
                        <label for="vocab-start" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Start Vocabulary ID
                        </label>
                        <input type="number" id="vocab-start" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="1" min="1" max="999">
                    </div>
                    <div>
                        <label for="vocab-end" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            End Vocabulary ID
                        </label>
                        <input type="number" id="vocab-end" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="20" min="1" max="999">
                    </div>
                </div>
                <div class="mt-4">
                    <button id="generate-forms" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md font-medium transition-colors">
                        Generate Vocabulary Forms
                    </button>
                </div>
            </div>

            <!-- Dynamic Forms Section -->
            <div id="vocabulary-forms" class="space-y-6" style="display: none;">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Vocabulary Items</h3>
                </div>
                <div id="forms-container"></div>
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
class VocabularyJSONGenerator {
    constructor() {
        this.lessonId = '';
        this.vocabStart = 0;
        this.vocabEnd = 0;
        this.vocabularyData = {};
        this.bindEvents();
    }

    bindEvents() {
        document.getElementById('generate-forms').addEventListener('click', () => this.generateForms());
        document.getElementById('generate-json').addEventListener('click', () => this.generateJSON());
        document.getElementById('copy-json').addEventListener('click', () => this.copyJSON());
        document.getElementById('download-json').addEventListener('click', () => this.downloadJSON());
    }

    generateForms() {
        const lessonId = document.getElementById('lesson-id').value.trim().padStart(2, '0');
        const vocabStart = parseInt(document.getElementById('vocab-start').value);
        const vocabEnd = parseInt(document.getElementById('vocab-end').value);

        if (!lessonId || !vocabStart || !vocabEnd) {
            alert('Please fill in all configuration fields.');
            return;
        }

        if (vocabStart > vocabEnd) {
            alert('Start vocabulary ID must be less than or equal to end vocabulary ID.');
            return;
        }

        if (vocabEnd - vocabStart + 1 > 50) {
            alert('Please limit to 50 vocabulary items or fewer.');
            return;
        }

        this.lessonId = lessonId;
        this.vocabStart = vocabStart;
        this.vocabEnd = vocabEnd;

        this.renderForms();
    }

    renderForms() {
        const container = document.getElementById('forms-container');
        const formsSection = document.getElementById('vocabulary-forms');
        
        container.innerHTML = '';
        
        for (let i = this.vocabStart; i <= this.vocabEnd; i++) {
            const vocabId = `mnn-${this.lessonId}-${i.toString().padStart(3, '0')}`;
            const formHtml = this.createVocabularyForm(vocabId, i);
            container.appendChild(formHtml);
        }
        
        formsSection.style.display = 'block';
        
        // Add event listeners for audio filename cleanup
        this.bindAudioFilenameCleanup();
    }

    createVocabularyForm(vocabId, index) {
        const div = document.createElement('div');
        div.className = 'border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-gray-50 dark:bg-gray-800';
        div.innerHTML = `
            <div class="mb-4">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                    <span class="mr-2">📚</span>
                    Vocabulary ${index}: ${vocabId}
                </h4>
            </div>
            
            <!-- Basic Word Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Japanese *</label>
                    <input type="text" data-field="word.japanese" data-vocab="${vocabId}" class="vocab-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="ひらがな/カタカナ/漢字">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Furigana</label>
                    <input type="text" data-field="word.furigana" data-vocab="${vocabId}" class="vocab-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="{漢字|かん|じ}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">English *</label>
                    <input type="text" data-field="word.english" data-vocab="${vocabId}" class="vocab-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="English meaning">
                </div>
            </div>

            <!-- Linguistic Information -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Part of Speech (select multiple)</label>
                    <div class="grid grid-cols-2 gap-2 p-3 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700">
                        <label class="flex items-center">
                            <input type="checkbox" value="noun" data-field="part_of_speech" data-vocab="${vocabId}" class="vocab-input rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Noun</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" value="verb" data-field="part_of_speech" data-vocab="${vocabId}" class="vocab-input rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Verb</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" value="adjective" data-field="part_of_speech" data-vocab="${vocabId}" class="vocab-input rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Adjective</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" value="adverb" data-field="part_of_speech" data-vocab="${vocabId}" class="vocab-input rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Adverb</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" value="particle" data-field="part_of_speech" data-vocab="${vocabId}" class="vocab-input rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Particle</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" value="conjunction" data-field="part_of_speech" data-vocab="${vocabId}" class="vocab-input rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Conjunction</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" value="interjection" data-field="part_of_speech" data-vocab="${vocabId}" class="vocab-input rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Interjection</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" value="counter" data-field="part_of_speech" data-vocab="${vocabId}" class="vocab-input rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Counter</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" value="expression" data-field="part_of_speech" data-vocab="${vocabId}" class="vocab-input rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Expression</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" value="affix" data-field="part_of_speech" data-vocab="${vocabId}" class="vocab-input rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Affix</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" value="kanji" data-field="part_of_speech" data-vocab="${vocabId}" class="vocab-input rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Kanji</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Verb Type</label>
                    <select data-field="verb_type" data-vocab="${vocabId}" class="vocab-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select...</option>
                        <option value="ichidan">Ichidan</option>
                        <option value="godan">Godan</option>
                        <option value="irregular">Irregular</option>
                        <option value="suru">Suru</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Adjective Type</label>
                    <select data-field="adjective_type" data-vocab="${vocabId}" class="vocab-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select...</option>
                        <option value="i_adjective">I-Adjective</option>
                        <option value="na_adjective">Na-Adjective</option>
                        <option value="no_adjective">No-Adjective</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">JLPT Level</label>
                    <select data-field="jlpt_level" data-vocab="${vocabId}" class="vocab-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select...</option>
                        <option value="N5">N5</option>
                        <option value="N4">N4</option>
                        <option value="N3">N3</option>
                        <option value="N2">N2</option>
                        <option value="N1">N1</option>
                    </select>
                </div>
            </div>

            <!-- Additional Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Audio Filename</label>
                    <input type="text" data-field="audio.filename" data-vocab="${vocabId}" class="vocab-input audio-filename-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="MNN_何々.mp3">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Frequency Rank</label>
                    <input type="number" data-field="frequency_rank" data-vocab="${vocabId}" class="vocab-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="1000">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mnemonics</label>
                <textarea data-field="mnemonics" data-vocab="${vocabId}" class="vocab-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" rows="2" placeholder="Memory aids or learning tips"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tags (comma-separated)</label>
                <input type="text" data-field="tags" data-vocab="${vocabId}" class="vocab-input w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="beginner, daily-use, family">
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" data-field="include_in_kanji_worksheet" data-vocab="${vocabId}" class="vocab-input rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Include in Kanji Worksheet</span>
                </label>
            </div>
        `;
        
        return div;
    }

    bindAudioFilenameCleanup() {
        // Add event listeners to all audio filename inputs for automatic cleanup
        const audioInputs = document.querySelectorAll('.audio-filename-input');
        audioInputs.forEach(input => {
            input.addEventListener('input', (e) => this.cleanupAudioFilename(e.target));
            input.addEventListener('paste', (e) => {
                // Small delay to allow paste content to be processed
                setTimeout(() => this.cleanupAudioFilename(e.target), 10);
            });
        });
    }

    cleanupAudioFilename(input) {
        let value = input.value;
        
        // Check if value matches the [sound:filename] format
        const soundPattern = /^\[sound:(.+)\]$/;
        const match = value.match(soundPattern);
        
        if (match) {
            // Extract just the filename part
            const cleanedValue = match[1];
            input.value = cleanedValue;
            
            // Add visual feedback for the cleanup
            input.classList.add('ring-2', 'ring-green-500');
            setTimeout(() => {
                input.classList.remove('ring-2', 'ring-green-500');
            }, 1000);
        }
    }

    generateJSON() {
        const vocabulary = [];
        
        for (let i = this.vocabStart; i <= this.vocabEnd; i++) {
            const vocabId = `mnn-${this.lessonId}-${i.toString().padStart(3, '0')}`;
            const vocabData = this.collectVocabularyData(vocabId);
            
            if (vocabData) {
                vocabulary.push(vocabData);
            }
        }

        if (vocabulary.length === 0) {
            alert('No vocabulary data found. Please fill in at least the required fields (Japanese, English, Part of Speech) for at least one vocabulary item.');
            return;
        }

        const jsonData = { vocabulary };
        const jsonString = JSON.stringify(jsonData, null, 2);
        
        document.getElementById('json-output').textContent = jsonString;
        document.getElementById('output-section').style.display = 'block';
        document.getElementById('download-json').style.display = 'inline-block';
        
        // Scroll to output
        document.getElementById('output-section').scrollIntoView({ behavior: 'smooth' });
    }

    collectVocabularyData(vocabId) {
        const inputs = document.querySelectorAll(`[data-vocab="${vocabId}"]`);
        const data = {
            id: vocabId,
            lesson_id: `mnn-lesson-${this.lessonId}`,
            word: {},
            audio: {
                filename: ""
            }
        };

        let hasRequiredData = false;
        
        inputs.forEach(input => {
            const field = input.dataset.field;
            
            // Handle different field types
            if (field.startsWith('word.')) {
                let value = input.value.trim();
                if (!value) return;
                const wordField = field.split('.')[1];
                data.word[wordField] = value;
                
                // Check for required fields
                if (field === 'word.japanese' || field === 'word.english') {
                    hasRequiredData = true;
                }
            } else if (field.startsWith('audio.')) {
                let value = input.value.trim();
                if (!value) return;
                const audioField = field.split('.')[1];
                data.audio[audioField] = value;
            } else if (field === 'part_of_speech') {
                // Handle multiple part of speech checkboxes
                if (input.checked) {
                    if (!data[field]) data[field] = [];
                    data[field].push(input.value);
                    hasRequiredData = true; // At least one part of speech selected
                }
            } else if (field === 'tags') {
                let value = input.value.trim();
                if (!value) return;
                data[field] = value.split(',').map(tag => tag.trim()).filter(tag => tag.length > 0);
            } else if (field === 'frequency_rank') {
                let value = input.value.trim();
                if (!value) return;
                data[field] = parseInt(value) || 0;
            } else if (field === 'include_in_kanji_worksheet') {
                data[field] = input.checked;
            } else {
                let value = input.value.trim();
                if (!value) return;
                data[field] = value;
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

// Initialize the generator when the page loads
document.addEventListener('DOMContentLoaded', () => {
    new VocabularyJSONGenerator();
});
</script>

@endsection
