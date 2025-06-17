@props([
    'size' => 'base',
])

@php
    $classes = match ($size) {
        'xs' => 'text-xs',
        'sm' => 'text-sm',
        'base' => 'text-base',
        'lg' => 'text-lg',
        'xl' => 'text-xl',
        '2xl' => 'text-2xl',
        default => 'text-base',
    };
@endphp

<p {{ $attributes->class("$classes text-zinc-700 dark:text-zinc-300") }}>
    {{ $slot }}
</p>
