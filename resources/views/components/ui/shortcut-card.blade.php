@props([
    'title',
    'description',
    'href' => null,
    'label' => 'Buka',
])

<div class="rounded-lg border border-green-100 bg-white p-5 shadow-sm">
    <div class="flex h-full flex-col justify-between">
        <div>
            <h4 class="font-semibold text-gray-900">
                {{ $title }}
            </h4>

            <p class="mt-2 text-sm text-gray-500">
                {{ $description }}
            </p>
        </div>

        <div class="mt-5">
            @if ($href)
                <a
                    href="{{ $href }}"
                    class="inline-flex items-center rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    {{ $label }}
                </a>
            @else
                <span class="inline-flex items-center rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-500">
                    Belum tersedia
                </span>
            @endif
        </div>
    </div>
</div>
