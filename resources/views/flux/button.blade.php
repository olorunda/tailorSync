@props([
    'variant' => 'primary',
    'type' => 'button',
    'disabled' => false,
])

@php
    $classes = match ($variant) {
        'primary' => 'bg-orange-600 hover:bg-orange-700 text-white',
        'secondary' => 'bg-zinc-200 hover:bg-zinc-300 text-zinc-900 dark:bg-zinc-700 dark:hover:bg-zinc-600 dark:text-white',
        'outline' => 'border border-zinc-300 hover:bg-zinc-100 text-zinc-900 dark:border-zinc-600 dark:hover:bg-zinc-800 dark:text-white',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white',
        default => 'bg-orange-600 hover:bg-orange-700 text-white',
    };

    $baseClasses = 'px-6 py-3 rounded-md font-medium transition-colors';

    if ($disabled) {
        $classes .= ' opacity-50 cursor-not-allowed';
    }
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class("$baseClasses $classes") }}
    @if ($disabled) disabled @endif
>
    {{ $slot }}
</button>
