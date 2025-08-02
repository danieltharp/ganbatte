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
    const furiganaElements = document.querySelectorAll('.furigana-text');
    const toggleButton = document.getElementById('furigana-toggle');
    
    furiganaElements.forEach(element => {
        const isEnabled = element.getAttribute('data-furigana-enabled') === 'true';
        
        if (isEnabled) {
            // Hide furigana by hiding rt elements
            element.querySelectorAll('rt').forEach(rt => rt.style.display = 'none');
            element.setAttribute('data-furigana-enabled', 'false');
            toggleButton.classList.add('opacity-50');
        } else {
            // Show furigana
            element.querySelectorAll('rt').forEach(rt => rt.style.display = '');
            element.setAttribute('data-furigana-enabled', 'true');
            toggleButton.classList.remove('opacity-50');
        }
    });
    
    // Store preference in localStorage
    localStorage.setItem('furigana-enabled', !furiganaElements[0] || furiganaElements[0].getAttribute('data-furigana-enabled') === 'true');
}

// Initialize furigana state from localStorage on page load
document.addEventListener('DOMContentLoaded', function() {
    const furiganaEnabled = localStorage.getItem('furigana-enabled');
    const toggleButton = document.getElementById('furigana-toggle');
    
    if (furiganaEnabled === 'false') {
        // Hide furigana on load
        document.querySelectorAll('.furigana-text').forEach(element => {
            element.querySelectorAll('rt').forEach(rt => rt.style.display = 'none');
            element.setAttribute('data-furigana-enabled', 'false');
        });
        toggleButton.classList.add('opacity-50');
    }
});
</script>

@push('scripts')
<script src="{{ asset('js/furigana.js') }}"></script>
@endpush 