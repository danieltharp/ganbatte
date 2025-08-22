@extends('layouts.app')

@section('title', 'Contribute to Ganbatte')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">Contribute to Ganbatte</h1>
        <p class="text-lg text-gray-600 dark:text-gray-400">
            Help improve and expand our Japanese learning platform by creating content using our specialized tools. 
            Each tool is designed to make content creation efficient while maintaining consistency and quality.
            Contributors should start by joining the <a href="https://discord.gg/jetpZY8s9w" target="_blank" class="text-blue-500 hover:text-blue-700">Discord server</a>.
        </p>
    </div>

    <!-- Content Generation Tools -->
    <div class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
            <span class="mr-2">🛠️</span>
            Content Generation Tools
        </h2>
        <p class="text-lg text-gray-600 dark:text-gray-400">
            These tools are highly specialized and require training, but are the most needed content for the site. 
            Please join the <a href="https://discord.gg/jetpZY8s9w" target="_blank" class="text-blue-500 hover:text-blue-700">Discord server</a> to get access to the tools and training.
        </p>
        <br>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Vocabulary JSON Generator -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-4">
                            <span class="text-2xl">📝</span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Vocabulary JSON Generator</h3>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Create structured vocabulary lesson JSON files with proper formatting. Generate forms for multiple vocabulary items and export as downloadable JSON files.
                    </p>
                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Ready to Use</span>
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Training Required</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <a href="{{ route('contribute.vocabulary.generator') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md font-medium transition-colors">
                            Open Generator →
                        </a>
                        <span class="text-sm text-gray-500 dark:text-gray-400">JSON Export</span>
                    </div>
                </div>
            </div>

            <!-- Grammar JSON Generator - Coming Soon -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700 opacity-60">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-4">
                            <span class="text-2xl">📐</span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Grammar JSON Generator</h3>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Generate grammar lesson JSON files with examples, usage patterns, and exercise data. Perfect for creating comprehensive grammar content.
                    </p>
                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Coming Soon</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <button disabled class="bg-gray-400 text-white px-4 py-2 rounded-md font-medium cursor-not-allowed">
                            Coming Soon
                        </button>
                        <span class="text-sm text-gray-500 dark:text-gray-400">JSON Export</span>
                    </div>
                </div>
            </div>

            <!-- Question Generator - Coming Soon -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700 opacity-60">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center mr-4">
                            <span class="text-2xl">❓</span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Question Generator</h3>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Create quiz questions and exercises for vocabulary and grammar testing. Generate multiple question types with proper answer formatting.
                    </p>
                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Coming Soon</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <button disabled class="bg-gray-400 text-white px-4 py-2 rounded-md font-medium cursor-not-allowed">
                            Coming Soon
                        </button>
                        <span class="text-sm text-gray-500 dark:text-gray-400">JSON Export</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Management Section -->
    @auth
        @if(Auth::user()->canManageContributions())
            <div class="mb-12">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
                    <span class="mr-2">⚙️</span>
                    Staff Tools
                </h2>
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-indigo-200 dark:border-indigo-700">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-4">
                                <span class="text-2xl">📋</span>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Manage Contributions</h3>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Review and process community contributions. View submitted suggestions, improvements, and corrections from users across the platform.
                        </p>
                        <div class="mb-4">
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-blue-200">Staff Access</span>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">JSON Preview</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <a href="{{ route('contribute.manage') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-md font-medium transition-colors">
                                Open Management Interface →
                            </a>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Review & Approve</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    <!-- Other Ways to Contribute -->
    <div class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
            <span class="mr-2">🤝</span>
            Other Ways to Contribute
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Page Contributions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">💡</span>
                        Page Contributions
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        The easiest way to contribute! While browsing vocabulary, lessons, or other content, click the "Contribute" button 
                        to suggest improvements, add example sentences, memory aids, or corrections directly from the content you're studying.
                    </p>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                        <li>• Add memory aids and mnemonics for vocabulary</li>
                        <li>• Suggest example sentences and usage notes</li>
                        <li>• Propose corrections or alternative meanings</li>
                        <li>• Share pronunciation tips and cultural context</li>
                        <li>• <strong>Earn Contributor status in our Discord community!</strong></li>
                    </ul>
                </div>
            </div>

            <!-- Content Review -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">🔍</span>
                        Content Review & Quality Assurance
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Help review existing content for accuracy, completeness, and consistency. This includes checking vocabulary translations, grammar explanations, and exercise quality.
                    </p>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                        <li>• Verify vocabulary translations and readings</li>
                        <li>• Check grammar explanations for clarity</li>
                        <li>• Test exercises for correctness</li>
                        <li>• Suggest improvements and corrections</li>
                    </ul>
                </div>
            </div>

            <!-- Audio Contributions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">🎵</span>
                        Audio Contributions
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Native speakers can contribute audio recordings for vocabulary pronunciation, example sentences, and listening exercises.
                    </p>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                        <li>• Record vocabulary pronunciation</li>
                        <li>• Provide example sentence audio</li>
                        <li>• Create listening exercise content</li>
                        <li>• Review audio quality and clarity</li>
                    </ul>
                </div>
            </div>

            <!-- Documentation -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">📚</span>
                        Documentation & Guides
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Help create and maintain documentation, study guides, and learning resources to make the platform more accessible and useful.
                    </p>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                        <li>• Write study guides and explanations</li>
                        <li>• Create usage tutorials</li>
                        <li>• Develop learning strategies</li>
                        <li>• Translate content to other languages</li>
                    </ul>
                </div>
            </div>

            <!-- Community Support -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <span class="mr-2">💬</span>
                        Community Support
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Help other learners by answering questions, providing study tips, and sharing learning experiences within the community.
                    </p>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                        <li>• Answer learner questions</li>
                        <li>• Share study techniques</li>
                        <li>• Provide cultural context</li>
                        <li>• Moderate community discussions</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Getting Started -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
            <span class="mr-2">🚀</span>
            Getting Started
        </h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            Ready to contribute? Here's how to get started:
        </p>
        <ol class="list-decimal list-inside text-gray-700 dark:text-gray-300 space-y-2">
            <li><strong>Start simple:</strong> Browse vocabulary or lessons and use the "Contribute" button to suggest improvements</li>
            <li><strong>Be specific:</strong> Clear, helpful suggestions get approved faster and help other learners more</li>
            <li><strong>Stay engaged:</strong> Regular contributors earn recognition and Contributor status in our Discord</li>
            <li><strong>For advanced tools:</strong> Content generation tools require proper training and access to resources</li>
            <li><strong>Quality matters:</strong> All contributions are reviewed to maintain platform standards</li>
        </ol>
        <div class="mt-6">
            <a href="https://discord.gg/jetpZY8s9w" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md font-medium transition-colors">
                Join Discord →
            </a>
        </div>
        <div class="mt-6">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <strong>Note:</strong> Content generation tools require specific training and access to resources. 
                For page contributions (the easiest way to help!), simply look for the "Contribute" button 
                while browsing content. Regular contributors earn Contributor status and recognition in our 
                <a href="https://discord.gg/jetpZY8s9w" target="_blank" class="text-blue-500 hover:text-blue-700">Discord community</a>.
            </p>
        </div>
    </div>
</div>
@endsection