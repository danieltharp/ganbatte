@props(['text' => 'Toggle Furigana', 'class' => 'bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded'])

<button 
    id="furigana-toggle"
    class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded-full shadow-lg border border-gray-200 dark:border-gray-600 transition-all duration-200"
    onclick="toggleFurigana()"
    title="Toggle furigana visibility">
    <span class="flex items-center space-x-2">
        <span class="text-sm">あ</span>
        <span class="text-xs text-gray-500 dark:text-gray-400">|</span>
        <span class="text-sm">漢</span>
    </span>
</button>

<script>
function toggleFurigana() {
    // Use the same logic as the original furigana.js script
    var rubies = document.querySelectorAll(".furigana");
    for(let ruby of rubies) {
        ruby.classList.toggle("no-furigana");
    }
    
    // Update button appearance - check if ANY furigana elements have no-furigana class
    const toggleButton = document.getElementById('furigana-toggle');
    const hiddenFurigana = document.querySelectorAll(".furigana.no-furigana");
    const allFurigana = document.querySelectorAll(".furigana");
    
    // If all furigana elements have no-furigana class, then furigana is hidden
    const furiganaHidden = hiddenFurigana.length === allFurigana.length && allFurigana.length > 0;
    
    if (furiganaHidden) {
        toggleButton.classList.add('opacity-50');
        localStorage.setItem('furigana-enabled', 'false');
    } else {
        toggleButton.classList.remove('opacity-50');
        localStorage.setItem('furigana-enabled', 'true');
    }
}

// Initialize furigana state from localStorage on page load
document.addEventListener('DOMContentLoaded', function() {
    const furiganaEnabled = localStorage.getItem('furigana-enabled');
    const toggleButton = document.getElementById('furigana-toggle');
    
    if (furiganaEnabled === 'false') {
        // Hide furigana on load by adding no-furigana class
        document.querySelectorAll('.furigana').forEach(element => {
            element.classList.add('no-furigana');
        });
        if (toggleButton) {
            toggleButton.classList.add('opacity-50');
        }
    }
});
</script>

@push('scripts')
<script src="{{ asset('js/furigana.js') }}"></script>
@endpush 