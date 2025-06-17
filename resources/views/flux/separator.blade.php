@props([
    'variant' => 'default',
])

@php
    $classes = match ($variant) {
        'default' => 'border-zinc-200 dark:border-zinc-700',
        'subtle' => 'border-zinc-100 dark:border-zinc-800',
        default => 'border-zinc-200 dark:border-zinc-700',
    };
@endphp

<hr {{ $attributes->class("my-4 border-t $classes") }}>
