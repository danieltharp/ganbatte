@extends('layouts.app')

@section('title', 'Contribution Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <a href="{{ route('contribute.manage') }}" class="text-blue-500 hover:text-blue-700 mr-4">
                ← Back to Manage Contributions
            </a>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                @if($contribution->status === 'new') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                @elseif($contribution->status === 'accepted') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                {{ ucfirst($contribution->status) }}
            </span>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">
            Contribution #{{ $contribution->id }}
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400">
            Contributing to {{ ucfirst($contribution->object_type) }}: {{ $contribution->object_id }}
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Column: Contribution Details -->
        <div class="space-y-6">
            <!-- Contribution Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Contribution Details</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Submitted by:</span>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $contribution->user->name }}
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    ({{ $contribution->user->accepted_contributions }} accepted, {{ $contribution->user->contribution_acceptance_rate }}% rate)
                                </span>
                            </div>
                        </div>

                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Submitted:</span>
                            <div class="text-gray-900 dark:text-gray-100">{{ $contribution->created_at->format('M j, Y g:i A') }}</div>
                        </div>

                        @if($contribution->reviewer)
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Reviewed by:</span>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $contribution->reviewer->name }} on {{ $contribution->reviewed_at->format('M j, Y g:i A') }}
                            </div>
                        </div>
                        @endif

                        @if($contribution->field_type)
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Field Type:</span>
                            <div class="text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $contribution->field_type)) }}</div>
                        </div>
                        @endif

                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Lesson:</span>
                            <div class="text-gray-900 dark:text-gray-100">
                                @if($contribution->lesson)
                                    {{ $contribution->lesson->title_english }}
                                @else
                                    {{ $contribution->lesson_id }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contribution Text -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Contribution Text</h2>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $contribution->contribution_text }}</div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Actions</h2>
                    <div class="flex space-x-3">
                        @if($contribution->isNew())
                            <button onclick="updateContributionStatus({{ $contribution->id }}, 'accepted')" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded font-medium transition-colors">
                                ✓ Accept Contribution
                            </button>
                            <button onclick="deleteContribution({{ $contribution->id }})" 
                                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded font-medium transition-colors">
                                ✗ Reject Contribution
                            </button>
                        @elseif($contribution->isAccepted())
                            <button onclick="updateContributionStatus({{ $contribution->id }}, 'completed')" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-medium transition-colors">
                                ✓ Mark as Completed
                            </button>
                        @elseif($contribution->isCompleted())
                            <button onclick="deleteCompletedContribution({{ $contribution->id }})" 
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded font-medium transition-colors">
                                🗑️ Remove from Queue
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: JSON Preview -->
        <div class="space-y-6">
            <!-- Field Selector -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">JSON Implementation Helper</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="target-field" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Select JSON field to modify:
                            </label>
                            <select id="target-field" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Choose a field...</option>
                                @foreach($availableFields as $fieldPath => $fieldLabel)
                                    <option value="{{ $fieldPath }}">{{ $fieldLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Standard field input -->
                        <div id="standard-input">
                            <label for="field-value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                New value:
                            </label>
                            <textarea id="field-value" rows="3" 
                                      class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                      placeholder="Enter the new value for this field..."></textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                For arrays, enter comma-separated values. For objects, use JSON format.
                            </p>
                        </div>

                        <!-- Example sentence structured input -->
                        <div id="example-sentence-input" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Example Sentence:
                            </label>
                            <div class="space-y-3">
                                <div>
                                    <label for="example-japanese" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Japanese</label>
                                    <input type="text" id="example-japanese" 
                                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                           placeholder="私は学生です。">
                                </div>
                                <div>
                                    <label for="example-furigana" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Furigana (optional)</label>
                                    <input type="text" id="example-furigana" 
                                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                           placeholder="{私|わたし}は{学生|がく|せい}です。">
                                </div>
                                <div>
                                    <label for="example-english" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">English</label>
                                    <input type="text" id="example-english" 
                                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                           placeholder="I am a student.">
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                This will be added to the existing example sentences array. Leave furigana blank if not needed.
                            </p>
                        </div>

                        <button onclick="updatePreview()" 
                                class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded font-medium transition-colors">
                            🔄 Update Preview
                        </button>
                    </div>
                </div>
            </div>

            <!-- Current Object -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Object</h3>
                    <div class="bg-gray-900 rounded-lg p-4">
                        <pre id="current-json" class="text-green-400 text-sm overflow-auto max-h-96 font-mono">{{ json_encode($jsonObject, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Updated Preview</h3>
                        <button onclick="copyPreviewJson()" 
                                class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm transition-colors">
                            Copy JSON
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-4">
                        <pre id="preview-json" class="text-green-400 text-sm overflow-auto max-h-96 font-mono">Select a field and enter a value to see the preview.</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentObject = @json($jsonObject);

// Handle field selection changes
document.getElementById('target-field').addEventListener('change', function() {
    const fieldPath = this.value;
    const standardInput = document.getElementById('standard-input');
    const exampleInput = document.getElementById('example-sentence-input');
    
    if (fieldPath === 'example_sentences') {
        standardInput.classList.add('hidden');
        exampleInput.classList.remove('hidden');
    } else {
        standardInput.classList.remove('hidden');
        exampleInput.classList.add('hidden');
    }
    
    // Clear preview when changing fields
    document.getElementById('preview-json').textContent = 'Enter values and click "Update Preview" to see changes.';
});

function updatePreview() {
    const fieldPath = document.getElementById('target-field').value;
    
    if (!fieldPath) {
        document.getElementById('preview-json').textContent = 'Select a field to preview changes.';
        return;
    }

    let fieldValue;
    
    // Handle example sentences differently
    if (fieldPath === 'example_sentences') {
        const japanese = document.getElementById('example-japanese').value.trim();
        const furigana = document.getElementById('example-furigana').value.trim();
        const english = document.getElementById('example-english').value.trim();
        
        if (!japanese || !english) {
            document.getElementById('preview-json').textContent = 'Both Japanese and English are required for example sentences.';
            return;
        }
        
        // Create the example sentence object
        const exampleSentence = {
            japanese: japanese,
            english: english
        };
        
        if (furigana) {
            exampleSentence.furigana = furigana;
        }
        
        fieldValue = [exampleSentence]; // Wrap in array as this will be added to existing array
    } else {
        fieldValue = document.getElementById('field-value').value.trim();
        if (!fieldValue) {
            document.getElementById('preview-json').textContent = 'Enter a value to preview changes.';
            return;
        }
    }

    try {
        // Create a deep copy of the current object
        const updatedObject = JSON.parse(JSON.stringify(currentObject));
        
        // Apply the field update
        if (fieldPath === 'example_sentences') {
            // Add to existing example sentences array
            if (!updatedObject.example_sentences) {
                updatedObject.example_sentences = [];
            }
            updatedObject.example_sentences.push(fieldValue[0]); // Add the new sentence
        } else {
            setNestedValue(updatedObject, fieldPath, parseFieldValue(fieldPath, fieldValue));
        }
        
        // Display the updated JSON
        document.getElementById('preview-json').textContent = JSON.stringify(updatedObject, null, 2);
        
    } catch (error) {
        document.getElementById('preview-json').textContent = 'Error: ' + error.message;
    }
}

function setNestedValue(obj, path, value) {
    const keys = path.split('.');
    let current = obj;
    
    for (let i = 0; i < keys.length - 1; i++) {
        const key = keys[i];
        if (!(key in current) || typeof current[key] !== 'object') {
            current[key] = {};
        }
        current = current[key];
    }
    
    current[keys[keys.length - 1]] = value;
}

function parseFieldValue(fieldPath, value) {
    // Handle different field types based on path and hints
    if (fieldPath.includes('part_of_speech') || fieldPath.includes('tags') || fieldPath.includes('prerequisites')) {
        // Array fields
        return value.split(',').map(item => item.trim()).filter(item => item.length > 0);
    } else if (fieldPath.includes('frequency_rank') || fieldPath.includes('estimated_time_minutes')) {
        // Numeric fields
        const num = parseInt(value);
        return isNaN(num) ? 0 : num;
    } else if (fieldPath.includes('include_in_kanji_worksheet')) {
        // Boolean fields
        return ['true', '1', 'yes', 'on'].includes(value.toLowerCase());
    } else if (fieldPath.includes('example_sentences') || fieldPath.includes('related_words')) {
        // Complex array/object fields - try to parse as JSON
        try {
            return JSON.parse(value);
        } catch (e) {
            // If not valid JSON, treat as simple array
            return value.split(',').map(item => item.trim()).filter(item => item.length > 0);
        }
    } else {
        // String fields
        return value;
    }
}

function copyPreviewJson() {
    const previewText = document.getElementById('preview-json').textContent;
    
    if (previewText.startsWith('Select a field') || previewText.startsWith('Enter a value') || previewText.startsWith('Error:')) {
        alert('No valid JSON to copy. Please generate a preview first.');
        return;
    }
    
    navigator.clipboard.writeText(previewText).then(() => {
        const button = event.target;
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

// Status update functions (same as manage page)
async function updateContributionStatus(contributionId, status) {
    const button = event.target;
    const originalText = button.textContent;
    
    button.disabled = true;
    button.textContent = 'Processing...';
    
    try {
        const response = await fetch(`/contributions/${contributionId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ status: status })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Failed to update status');
        }

        showNotification(data.message, 'success');
        setTimeout(() => window.location.reload(), 1000);

    } catch (error) {
        console.error('Error updating contribution:', error);
        showNotification(error.message || 'Failed to update contribution status', 'error');
        
        button.disabled = false;
        button.textContent = originalText;
    }
}

async function deleteContribution(contributionId) {
    if (!confirm('Are you sure you want to reject and delete this contribution?')) {
        return;
    }

    try {
        const response = await fetch(`/contributions/${contributionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Failed to delete contribution');
        }

        showNotification(data.message, 'success');
        setTimeout(() => window.location.href = '{{ route('contribute.manage') }}', 1000);

    } catch (error) {
        console.error('Error deleting contribution:', error);
        showNotification(error.message || 'Failed to delete contribution', 'error');
    }
}

async function deleteCompletedContribution(contributionId) {
    if (!confirm('Remove this completed contribution from the queue?')) {
        return;
    }

    try {
        const response = await fetch(`/contributions/${contributionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Failed to remove contribution');
        }

        showNotification(data.message, 'success');
        setTimeout(() => window.location.href = '{{ route('contribute.manage') }}', 1000);

    } catch (error) {
        console.error('Error removing contribution:', error);
        showNotification(error.message || 'Failed to remove contribution', 'error');
    }
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-3 rounded shadow-lg z-50 ${
        type === 'success' 
            ? 'bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-400'
            : 'bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-400'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transition = 'all 0.3s ease-out';
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>

@endsection
