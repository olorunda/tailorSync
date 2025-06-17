@props([
    'label' => null,
    'disabled' => false,
])

<div {{ $attributes->only(['class'])->class('flex items-center') }}>
    <input
        {{ $attributes->except(['class']) }}
        type="checkbox"
        @if ($disabled) disabled @endif
        class="h-4 w-4 rounded border-zinc-300 text-orange-600 focus:ring-orange-500/50 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:focus:ring-orange-500/50"
    >

    @if ($label)
        <label for="{{ $attributes->get('id') ?? $attributes->wire('model')->value() }}" class="ml-2 block text-sm text-zinc-700 dark:text-zinc-300">
            {{ $label }}
        </label>
    @endif

    @error($attributes->wire('model')->value())
        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>
