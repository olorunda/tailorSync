@props([
    'label' => null,
    'type' => 'text',
    'viewable' => false,
    'disabled' => false,
])

<div {{ $attributes->only(['class'])->class('relative') }}>
    @if ($label)
        <label for="{{ $attributes->get('id') ?? $attributes->wire('model')->value() }}" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <input
            {{ $attributes->except(['class']) }}
            type="{{ $type }}"
            @if ($disabled) disabled @endif
            class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/50 disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:focus:border-orange-500 dark:focus:ring-orange-500/50"
        >

        @if ($viewable && in_array($type, ['password']))
            <button
                type="button"
                x-data="{ show: false }"
                x-on:click="show = !show; $el.closest('div').querySelector('input').type = show ? 'text' : 'password'"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
            >
                <svg
                    x-show="!show"
                    class="h-5 w-5"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <svg
                    x-show="show"
                    x-cloak
                    class="h-5 w-5"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                    <line x1="2" x2="22" y1="2" y2="22" />
                </svg>
            </button>
        @endif
    </div>

    @error($attributes->wire('model')->value())
        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>
