@props([
    'size' => 'xl',
    'level' => 2,
])

@php
    $classes = match ($size) {
        'xs' => 'text-xs',
        'sm' => 'text-sm',
        'base' => 'text-base',
        'lg' => 'text-lg',
        'xl' => 'text-xl',
        '2xl' => 'text-2xl',
        '3xl' => 'text-3xl',
        '4xl' => 'text-4xl',
        default => 'text-xl',
    };

    $tag = match ((int) $level) {
        1 => 'h1',
        2 => 'h2',
        3 => 'h3',
        4 => 'h4',
        5 => 'h5',
        6 => 'h6',
        default => 'h2',
    };
@endphp

<{{ $tag }} {{ $attributes->class("$classes font-bold text-orange-600 dark:text-orange-500") }}>
    {{ $slot }}
</{{ $tag }}>
