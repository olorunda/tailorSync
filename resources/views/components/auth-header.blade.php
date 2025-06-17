@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center mb-4">
    <h2 class="text-2xl font-bold mb-2 text-orange-600 dark:text-orange-500">{{ $title }}</h2>
    <p class="text-zinc-600 dark:text-zinc-400">{{ $description }}</p>
</div>
