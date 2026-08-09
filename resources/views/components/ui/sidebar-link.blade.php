@props([
    'href',
    'active' => false,
])

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => $active
            ? 'flex items-center rounded-lg bg-green-700 px-3 py-2 text-sm font-medium text-white'
            : 'flex items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700',
    ]) }}
>
    {{ $slot }}
</a>
