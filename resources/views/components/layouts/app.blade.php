<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        <div class="sm:mb-0 mb-10">
        {{ $slot }}
        </div>
    </flux:main>
</x-layouts.app.sidebar>
