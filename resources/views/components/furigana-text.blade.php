@props(['text', 'class' => ''])

<span class="{{ $class }}">
    @if($text)
        {!! $text !!}
    @endif
</span> 