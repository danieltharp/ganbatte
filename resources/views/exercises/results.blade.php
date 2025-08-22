@extends('layouts.app')

@section('title', 'Exercise Results - ' . $attempt->exercise->name)

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="flex items-center space-x-4 mb-2">
                <a href="{{ route('exercises.show', $attempt->exercise->id) }}" class="text-blue-500 hover:text-blue-700">← Back to Exercise</a>
                <a href="{{ route('lessons.show', $attempt->exercise->lesson->id) }}" class="text-blue-500 hover:text-blue-700">← Back to Lesson</a>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                Exercise Results
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $attempt->exercise->name }}</p>
        </div>
        
        <div class="text-right">
            <div class="text-4xl font-bold mb-1 {{ $attempt->is_passed ? 'text-green-500' : 'text-orange-500' }}">
                {{ $attempt->percentage }}%
            </div>
            @if($attempt->hasManualCorrections())
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    (Originally {{ $attempt->original_percentage }}%)
                </div>
            @endif
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ $attempt->score }}/{{ $attempt->total_points }} points
            </div>
        </div>
    </div>
</div>

<!-- Score Summary -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $attempt->percentage }}%</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Final Score</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $attempt->duration }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Duration</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold {{ $attempt->is_passed ? 'text-green-500' : 'text-red-500' }}">
                    {{ $attempt->is_passed ? 'PASSED' : 'NEEDS REVIEW' }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Result</div>
            </div>
            @if($attempt->hasManualCorrections())
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-500">+{{ $attempt->manual_improvement }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Manual Corrections</div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Question Results -->
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Detailed Results</h2>
        
        <div class="space-y-6" id="question-results">
                            @foreach($questionResults as $index => $result)
                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 question-result bg-white dark:bg-gray-800" data-question-id="{{ $result['question_id'] }}">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                Question {{ $index + 1 }}
                                <span class="text-sm text-gray-500">({{ $result['question_type'] }})</span>
                            </h3>
                            <p class="text-gray-700 dark:text-gray-300 mb-3">{{ $result['question_text'] }}</p>
                        </div>
                        
                        <div class="flex items-center space-x-2 ml-4">
                            @if($result['is_correct'])
                                <span class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 px-3 py-1 rounded-full text-sm font-medium">
                                    ✅ Correct
                                </span>
                            @elseif($result['manually_accepted'] ?? false)
                                <span class="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-3 py-1 rounded-full text-sm font-medium">
                                    ✓ Manually Accepted
                                </span>
                            @else
                                <span class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 px-3 py-1 rounded-full text-sm font-medium">
                                    ❌ Incorrect
                                </span>
                            @endif
                            
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                {{ $result['points_earned'] }}/{{ $result['points_available'] }} pts
                            </span>
                        </div>
                    </div>
                    
                    <!-- Answer Comparison -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Your Answer:</h4>
                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded border border-gray-200 dark:border-gray-600">
                                <span class="japanese-text text-lg text-gray-900 dark:text-gray-100">
                                    @if(empty($result['user_answer']) || $result['user_answer'] === '')
                                        <em class="text-gray-500 dark:text-gray-400">(No answer provided)</em>
                                    @else
                                        {{ is_array($result['user_answer']) ? implode(', ', $result['user_answer']) : $result['user_answer'] }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Expected Answer:</h4>
                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded border border-gray-200 dark:border-gray-600">
                                <span class="japanese-text text-lg text-gray-900 dark:text-gray-100">
                                    {{ is_array($result['correct_answer']) ? implode(', ', $result['correct_answer']) : $result['correct_answer'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Manual Correction Option -->
                    @if($result['can_be_corrected'] ?? false)
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">
                                        Think your answer is also correct?
                                    </h4>
                                    <p class="text-xs text-yellow-700 dark:text-yellow-300 mb-3">
                                        You can mark this answer as correct if you believe it's a valid alternative that wasn't recognized.
                                    </p>
                                    
                                    <textarea 
                                        class="w-full text-sm border border-yellow-300 dark:border-yellow-600 rounded p-2 mb-3 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-yellow-500 dark:focus:ring-yellow-400 focus:border-transparent" 
                                        placeholder="Optional: Explain why you think this answer is correct..."
                                        id="reason-{{ $result['question_id'] }}"
                                        rows="2"
                                    ></textarea>
                                </div>
                                
                                <button 
                                    class="ml-4 bg-yellow-500 hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-500 text-white font-medium py-2 px-4 rounded text-sm transition-colors"
                                    onclick="acceptAnswer('{{ $result['question_id'] }}', {{ $attempt->id }})"
                                    id="accept-btn-{{ $result['question_id'] }}"
                                >
                                    Mark as Correct
                                </button>
                            </div>
                        </div>
                    @elseif($result['manually_accepted'] ?? false)
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3">
                            <div class="flex items-center">
                                <span class="text-blue-600 dark:text-blue-400 mr-2">ℹ️</span>
                                <div class="flex-1">
                                    <span class="text-sm text-blue-800 dark:text-blue-200 font-medium">
                                        This answer was manually accepted as correct.
                                    </span>
                                    @if($result['manual_reason'])
                                        <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                                            Reason: {{ $result['manual_reason'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Explanation -->
                    @if($result['explanation'])
                        <div class="mt-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Explanation:</h4>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $result['explanation'] }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
function acceptAnswer(questionId, attemptId) {
    const reasonTextarea = document.getElementById(`reason-${questionId}`);
    const acceptBtn = document.getElementById(`accept-btn-${questionId}`);
    const reason = reasonTextarea ? reasonTextarea.value.trim() : '';
    
    // Show loading state
    acceptBtn.disabled = true;
    acceptBtn.textContent = 'Accepting...';
    
    fetch(`/exercise-attempts/${attemptId}/accept-answer`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            question_id: questionId,
            reason: reason || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the question result display
            const questionDiv = document.querySelector(`[data-question-id="${questionId}"]`);
            
            // Update status badge
            const statusBadge = questionDiv.querySelector('.bg-red-100');
            if (statusBadge) {
                statusBadge.className = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-3 py-1 rounded-full text-sm font-medium';
                statusBadge.innerHTML = '✓ Manually Accepted';
            }
            
            // Update points display
            const pointsSpan = questionDiv.querySelector('.text-gray-600');
            if (pointsSpan) {
                pointsSpan.textContent = `${data.points_earned}/${data.points_earned} pts`;
            }
            
            // Remove manual correction form and add info box
            const correctionDiv = questionDiv.querySelector('.bg-yellow-50');
            if (correctionDiv) {
                correctionDiv.className = 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3';
                correctionDiv.innerHTML = `
                    <div class="flex items-center">
                        <span class="text-blue-600 dark:text-blue-400 mr-2">ℹ️</span>
                        <div class="flex-1">
                            <span class="text-sm text-blue-800 dark:text-blue-200 font-medium">
                                This answer was manually accepted as correct.
                            </span>
                            ${reason ? `<p class="text-xs text-blue-700 dark:text-blue-300 mt-1">Reason: ${reason}</p>` : ''}
                        </div>
                    </div>
                `;
            }
            
            // Update overall score in header
            const scoreElement = document.querySelector('.text-4xl.font-bold');
            if (scoreElement) {
                scoreElement.textContent = `${data.new_percentage}%`;
                if (data.new_percentage >= 70) {
                    scoreElement.className = scoreElement.className.replace('text-orange-500', 'text-green-500');
                }
            }
            
            // Show success message
            showMessage(`Answer accepted! Score improved to ${data.new_percentage}%`, 'success');
            
        } else {
            // Handle error
            showMessage('Error: ' + (data.message || 'Failed to accept answer'), 'error');
            resetAcceptButton(questionId);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Network error occurred. Please try again.', 'error');
        resetAcceptButton(questionId);
    });
}

function resetAcceptButton(questionId) {
    const acceptBtn = document.getElementById(`accept-btn-${questionId}`);
    acceptBtn.disabled = false;
    acceptBtn.textContent = 'Mark as Correct';
}

function showMessage(message, type) {
    // Create or update message div
    let messageDiv = document.getElementById('message-div');
    if (!messageDiv) {
        messageDiv = document.createElement('div');
        messageDiv.id = 'message-div';
        messageDiv.className = 'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg';
        document.body.appendChild(messageDiv);
    }
    
    messageDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    messageDiv.textContent = message;
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.parentNode.removeChild(messageDiv);
        }
    }, 3000);
}
</script>

@endsection
