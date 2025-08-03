<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Japanese Lesson with Furigana</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <!-- Toggle Button -->
        <div class="mb-6 text-right">
            <x-furigana-toggle />
        </div>

        <!-- Lesson Title -->
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <x-furigana-text :text="$lesson->furigana_title" />
            </h1>
            <p class="text-lg text-gray-600">{{ $lesson->title_english }}</p>
            <p class="text-sm text-gray-500">{{ $lesson->description }}</p>
        </header>

        <!-- Vocabulary Section -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Vocabulary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($lesson->vocabulary as $vocab)
                <div class="border rounded-lg p-4 bg-blue-50">
                    <div class="text-xl mb-2">
                        <x-furigana-text :text="$vocab->furigana_word" class="font-bold text-blue-800" />
                    </div>
                    <div class="text-gray-700">{{ $vocab->word_english }}</div>
                    <div class="text-sm text-gray-500 mt-1">{{ ucfirst($vocab->part_of_speech) }}</div>
                    
                    @if($vocab->example_sentences)
                        <div class="mt-3 pt-3 border-t border-blue-200">
                            <p class="text-sm font-medium text-gray-700 mb-1">Example:</p>
                            @foreach($vocab->example_sentences as $example)
                                <div class="text-sm">
                                    <div class="mb-1">
                                        <x-furigana-text :text="$example['furigana'] ?? $example['japanese']" />
                                    </div>
                                    <div class="text-gray-600">{{ $example['english'] }}</div>
                                </div>
                                @break {{-- Just show first example for brevity --}}
                            @endforeach
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        </section>

        <!-- Grammar Section -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Grammar Points</h2>
            @foreach($lesson->grammarPoints as $grammar)
            <div class="border rounded-lg p-4 mb-4 bg-green-50">
                <h3 class="text-lg font-semibold mb-2">
                    <x-furigana-text :text="$grammar->name_furigana ?? $grammar->display_name" class="text-green-800" />
                </h3>
                <div class="mb-2">
                    <span class="font-medium text-gray-700">Pattern:</span>
                    <code class="bg-gray-100 px-2 py-1 rounded">{{ $grammar->pattern }}</code>
                </div>
                <p class="text-gray-700 mb-3">{{ $grammar->explanation }}</p>
                
                @if($grammar->examples)
                    <div class="space-y-2">
                        <p class="font-medium text-gray-700">Examples:</p>
                        @foreach($grammar->examples as $example)
                        <div class="bg-white p-3 rounded border-l-4 border-green-400">
                            <div class="mb-1">
                                <x-furigana-text :text="$example['sentence']['furigana'] ?? $example['sentence']['japanese']" />
                            </div>
                            <div class="text-gray-600 text-sm">{{ $example['sentence']['english'] }}</div>
                            @if(isset($example['context']))
                                <div class="text-xs text-gray-500 mt-1">Context: {{ $example['context'] }}</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endforeach
        </section>

        <!-- Questions Section -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Practice Questions</h2>
            @foreach($lesson->questions as $index => $question)
            <div class="border rounded-lg p-4 mb-4 bg-yellow-50">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="font-semibold text-gray-800">Question {{ $index + 1 }}</h3>
                    <span class="bg-yellow-200 text-yellow-800 px-2 py-1 rounded text-xs">
                        {{ ucfirst(str_replace('_', ' ', $question->type)) }}
                    </span>
                </div>
                
                <div class="mb-3">
                    <x-furigana-text :text="$question->question_furigana ?? $question->question_text" class="text-lg" />
                </div>

                @if($question->options)
                    <div class="space-y-2 mb-3">
                        @foreach($question->options as $optionIndex => $option)
                        <div class="bg-white p-3 rounded border border-gray-200 hover:border-blue-300 cursor-pointer transition-colors option-item" 
                             data-question="{{ $question->id }}" 
                             data-option="{{ $optionIndex }}"
                             data-correct="{{ $question->correct_answer == $optionIndex ? 'true' : 'false' }}">
                            <div class="flex items-start space-x-3">
                                <span class="font-medium text-gray-600 bg-gray-100 rounded-full w-6 h-6 flex items-center justify-center text-sm">{{ chr(65 + $optionIndex) }}</span>
                                <div class="flex-1">
                                    <div class="mb-1">
                                        <x-furigana-text :text="$option['furigana'] ?? $option['english'] ?? $option" />
                                    </div>
                                    @if(isset($option['explanation']))
                                        <div class="option-explanation hidden mt-2 p-3 rounded text-sm" style="background-color: #f0f9ff; border-left: 3px solid #3b82f6;">
                                            <div class="font-medium text-blue-800 mb-1">Explanation:</div>
                                            <div class="text-blue-700">{{ $option['explanation'] }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif

                @if($question->explanation_text)
                    <div class="mt-3 pt-3 border-t border-yellow-200">
                        <p class="text-sm font-medium text-gray-700 mb-1">Overall Explanation:</p>
                        <div class="text-sm text-gray-600">
                            <x-furigana-text :text="$question->explanation_furigana ?? $question->explanation_text" />
                        </div>
                    </div>
                @endif
            </div>
            @endforeach
        </section>
    </div>

    @vite('resources/js/furigana.js')
    <script>
        // Initialize furigana rendering on page load
        document.addEventListener('DOMContentLoaded', function() {
            renderFurigana(document.body);
            
            // Add click handlers for option explanations
            document.querySelectorAll('.option-item').forEach(option => {
                option.addEventListener('click', function() {
                    const explanation = this.querySelector('.option-explanation');
                    const isCorrect = this.dataset.correct === 'true';
                    
                    if (explanation) {
                        // Show the explanation
                        explanation.classList.remove('hidden');
                        
                        // Style based on correctness
                        if (isCorrect) {
                            this.classList.add('border-green-400', 'bg-green-50');
                            explanation.style.backgroundColor = '#f0f9ff';
                            explanation.style.borderLeftColor = '#10b981';
                        } else {
                            this.classList.add('border-red-400', 'bg-red-50');  
                            explanation.style.backgroundColor = '#fef2f2';
                            explanation.style.borderLeftColor = '#ef4444';
                        }
                    }
                    
                    // Disable further clicks on this question
                    const questionId = this.dataset.question;
                    document.querySelectorAll(`[data-question="${questionId}"]`).forEach(opt => {
                        opt.style.pointerEvents = 'none';
                        opt.classList.add('opacity-75');
                    });
                });
            });
        });
    </script>
</body>
</html> 