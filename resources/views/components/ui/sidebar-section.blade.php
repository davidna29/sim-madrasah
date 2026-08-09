@props([
    'title',
])

<div class="mt-6 first:mt-0">
    <div class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
        {{ $title }}
    </div>

    <div class="mt-2 space-y-1">
        {{ $slot }}
    </div>
</div>
