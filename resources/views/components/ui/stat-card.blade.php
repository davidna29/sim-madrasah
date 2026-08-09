@props([
    'label',
    'value',
    'description' => null,
])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
    <div class="p-6">
        <div class="text-sm font-medium text-gray-500">
            {{ $label }}
        </div>

        <div class="mt-2 text-2xl font-semibold text-gray-900">
            {{ $value }}
        </div>

        @if ($description)
            <p class="mt-2 text-sm text-gray-500">
                {{ $description }}
            </p>
        @endif
    </div>
</div>