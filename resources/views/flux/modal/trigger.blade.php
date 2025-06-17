@props([
    'name' => null,
])

<div
    x-data
    x-on:click="$dispatch('open-modal', '{{ $name }}')"
    {{ $attributes }}
>
    {{ $slot }}
</div>
