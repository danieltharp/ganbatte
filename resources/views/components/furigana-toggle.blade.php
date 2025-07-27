@props(['text' => 'Toggle Furigana', 'class' => 'bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded'])

<button 
    onclick="toggleFurigana()" 
    class="{{ $class }}"
    type="button"
>
    {{ $text }}
</button>

@push('scripts')
<script src="{{ asset('js/furigana.js') }}"></script>
@endpush 