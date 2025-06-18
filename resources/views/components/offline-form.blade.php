@props(['action', 'method' => 'POST'])

<form {{ $attributes->merge(['action' => $action, 'method' => $method === 'GET' ? 'GET' : 'POST', 'data-offline' => 'true']) }}>
    @if ($method !== 'GET' && $method !== 'POST')
        @method($method)
    @endif

    @csrf

    {{ $slot }}
</form>
