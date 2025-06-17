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
        default => 'text-base',
    };
@endphp

<p {{ $attributes->class("$classes text-zinc-600 dark:text-zinc-400") }}>
    {{ $slot }}
</p>
