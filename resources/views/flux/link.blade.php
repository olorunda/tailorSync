@props([
    'href' => null,
    'external' => false,
])

@php
    $classes = 'text-orange-600 dark:text-orange-500 hover:text-orange-800 dark:hover:text-orange-400 transition-colors';
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->class($classes) }}
        @if ($external) target="_blank" rel="noopener noreferrer" @endif
    >
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
