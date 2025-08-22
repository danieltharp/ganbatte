@extends('layouts.app')

@section('title', 'Manage Contributions')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">Manage Contributions</h1>
                <p class="text-lg text-gray-600 dark:text-gray-400">Review and process community contributions to improve content quality.</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Total: {{ $contributions->total() }} contributions
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <form method="GET" action="{{ route('contribute.manage') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" id="status" class="block w-full text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
                        <option value="">All Statuses</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                
                <div>
                    <label for="lesson_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lesson</label>
                    <select name="lesson_id" id="lesson_id" class="block w-full text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
                        <option value="">All Lessons</option>
                        @foreach($lessons as $lesson)
                            <option value="{{ $lesson->id }}" {{ request('lesson_id') == $lesson->id ? 'selected' : '' }}>
                                {{ $lesson->title_english }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="object_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Content Type</label>
                    <select name="object_type" id="object_type" class="block w-full text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
                        <option value="">All Types</option>
                        @foreach($objectTypes as $type)
                            <option value="{{ $type }}" {{ request('object_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">&nbsp;</label>
                    <div class="flex space-x-2">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Filter
                        </button>
                        <a href="{{ route('contribute.manage') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Contributions List -->
    <div class="space-y-4">
        @forelse($contributions as $contribution)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700" 
                 data-contribution-id="{{ $contribution->id }}">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <!-- Status badge -->
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($contribution->status === 'new') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @elseif($contribution->status === 'accepted') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                                    {{ ucfirst($contribution->status) }}
                                </span>

                                <!-- Object type -->
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    {{ ucfirst(str_replace('_', ' ', $contribution->object_type)) }}
                                </span>

                                <!-- Field type -->
                                @if($contribution->field_type)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                        {{ ucfirst(str_replace('_', ' ', $contribution->field_type)) }}
                                    </span>
                                @endif

                                <!-- Lesson -->
                                @if($contribution->lesson)
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $contribution->lesson->title_english }}
                                    </span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Contributing to: {{ $contribution->object_id }} ({{ $contribution->object_details }})
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    By {{ $contribution->user->name }} • {{ $contribution->created_at->format('M j, Y g:i A') }}
                                    @if($contribution->reviewer)
                                        • Reviewed by {{ $contribution->reviewer->name }}
                                    @endif
                                </p>
                            </div>

                            <!-- Contribution text -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4 mr-4">
                                <div class="text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $contribution->contribution_text }}</div>
                            </div>

                            <!-- User stats -->
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Contributor stats: {{ $contribution->user->accepted_contributions }} accepted, 
                                    {{ $contribution->user->rejected_contributions }} rejected 
                                    ({{ $contribution->user->contribution_acceptance_rate }}% acceptance rate)
                                    @if(!$contribution->user->can_user_contribute)
                                        <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Blocked from contributing
                                        </span>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    @if($contribution->user->can_user_contribute)
                                        <button onclick="toggleUserContributeStatus({{ $contribution->user->id }}, false)" 
                                                class="text-xs bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded font-medium transition-colors">
                                            Block User
                                        </button>
                                    @else
                                        <button onclick="toggleUserContributeStatus({{ $contribution->user->id }}, true)" 
                                                class="text-xs bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded font-medium transition-colors">
                                            Unblock User
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="ml-6 flex flex-col space-y-2">
                            @if($contribution->isNew())
                                <button onclick="updateContributionStatus({{ $contribution->id }}, 'accepted')" 
                                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                    ✓ Accept
                                </button>
                                <button onclick="deleteContribution({{ $contribution->id }})" 
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                    ✗ Reject
                                </button>
                            @elseif($contribution->isAccepted())
                                <button onclick="updateContributionStatus({{ $contribution->id }}, 'completed')" 
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                    ✓ Complete
                                </button>
                            @elseif($contribution->isCompleted())
                                <button onclick="deleteCompletedContribution({{ $contribution->id }})" 
                                        class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                    🗑️ Remove
                                </button>
                            @endif
                            
                            <a href="{{ route('contributions.show', $contribution) }}" 
                               class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors text-center">
                                📋 Details
                            </a>
                            <a href="{{ route('contribute.manage') }}?object_type={{ $contribution->object_type }}&object_id={{ $contribution->object_id }}" 
                               class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors text-center">
                                🔗 Filter Similar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    <div class="text-gray-400 mb-4">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m6-6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No contributions found</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        @if(request()->hasAny(['status', 'lesson_id', 'object_type']))
                            Try adjusting your filters to see more contributions.
                        @else
                            No contributions have been submitted yet.
                        @endif
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($contributions->hasPages())
        <div class="mt-8">
            {{ $contributions->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<script>
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

        // Show success message
        showNotification(data.message, 'success');
        
        // Reload page to show updated status
        window.location.reload();

    } catch (error) {
        console.error('Error updating contribution:', error);
        showNotification(error.message || 'Failed to update contribution status', 'error');
        
        button.disabled = false;
        button.textContent = originalText;
    }
}

async function deleteContribution(contributionId) {
    if (!confirm('Are you sure you want to reject and delete this contribution? This action cannot be undone.')) {
        return;
    }

    const button = event.target;
    const originalText = button.textContent;
    
    button.disabled = true;
    button.textContent = 'Deleting...';
    
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

        // Show success message
        showNotification(data.message, 'success');
        
        // Remove the contribution from the page
        const contributionElement = document.querySelector(`[data-contribution-id="${contributionId}"]`);
        if (contributionElement) {
            contributionElement.style.transition = 'all 0.3s ease-out';
            contributionElement.style.opacity = '0';
            contributionElement.style.transform = 'translateX(100%)';
            setTimeout(() => contributionElement.remove(), 300);
        }

    } catch (error) {
        console.error('Error deleting contribution:', error);
        showNotification(error.message || 'Failed to delete contribution', 'error');
        
        button.disabled = false;
        button.textContent = originalText;
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

async function deleteCompletedContribution(contributionId) {
    if (!confirm('Remove this completed contribution from the queue? This will permanently delete the record.')) {
        return;
    }

    const button = event.target;
    const originalText = button.textContent;
    
    button.disabled = true;
    button.textContent = 'Removing...';
    
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

        // Show success message
        showNotification('Completed contribution removed from queue.', 'success');
        
        // Remove the contribution from the page
        const contributionElement = document.querySelector(`[data-contribution-id="${contributionId}"]`);
        if (contributionElement) {
            contributionElement.style.transition = 'all 0.3s ease-out';
            contributionElement.style.opacity = '0';
            contributionElement.style.transform = 'translateX(100%)';
            setTimeout(() => contributionElement.remove(), 300);
        }

    } catch (error) {
        console.error('Error removing contribution:', error);
        showNotification(error.message || 'Failed to remove contribution', 'error');
        
        button.disabled = false;
        button.textContent = originalText;
    }
}

async function toggleUserContributeStatus(userId, canContribute) {
    const action = canContribute ? 'unblock' : 'block';
    const actionText = canContribute ? 'unblocked' : 'blocked';
    
    if (!confirm(`Are you sure you want to ${action} this user from contributing?`)) {
        return;
    }

    const button = event.target;
    const originalText = button.textContent;
    
    button.disabled = true;
    button.textContent = `${canContribute ? 'Unblocking' : 'Blocking'}...`;
    
    try {
        const response = await fetch(`/admin/users/${userId}/toggle-contribute`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ can_user_contribute: canContribute })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || `Failed to ${action} user`);
        }

        // Show success message
        showNotification(`User has been ${actionText} from contributing.`, 'success');
        
        // Reload page to show updated status
        window.location.reload();

    } catch (error) {
        console.error(`Error ${action}ing user:`, error);
        showNotification(error.message || `Failed to ${action} user from contributing`, 'error');
        
        button.disabled = false;
        button.textContent = originalText;
    }
}
</script>

@endsection
