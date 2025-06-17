@props([
    'name' => null,
    'show' => false,
    'focusable' => true,
    'maxWidth' => '2xl',
])

<div
    x-data="{ show: @js($show) }"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    @if ($name)
    x-on:open-{{ $name }}.window="show = true"
    @endif
    x-show="show"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4 py-6 sm:px-0"
    style="display: none;"
>
    <!-- Backdrop -->
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-zinc-900/50 backdrop-blur-sm"
        x-on:click="show = false"
    ></div>

    <!-- Modal Content -->
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="transform rounded-lg bg-white p-6 shadow-xl transition-all dark:bg-zinc-800 sm:mx-auto relative z-[60] {{ $maxWidth === 'sm' ? 'sm:max-w-sm' : ($maxWidth === 'md' ? 'sm:max-w-md' : ($maxWidth === 'lg' ? 'sm:max-w-lg' : ($maxWidth === 'xl' ? 'sm:max-w-xl' : 'sm:max-w-2xl'))) }}"
        {{ $attributes }}
    >
        {{ $slot }}
    </div>
</div>

@if ($name)
    <script>
        window.addEventListener('open-modal', event => {
            if (event.detail === '{{ $name }}') {
                window.dispatchEvent(new CustomEvent('open-{{ $name }}'));
            }
        });
    </script>
@endif
