@props(['businessDetail', 'cart', 'title' => null])

<x-layouts.storefront :businessDetail="$businessDetail" :cart="$cart" :title="$title">
    {{ $slot }}
</x-layouts.storefront>
