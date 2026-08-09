@props([
    'title',
    'description',
    'href' => null,
    'status' => 'Menunggu modul',
])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
    <div class="p-6 flex h-full flex-col justify-between">
        <div>
            <div class="flex items-start justify-between gap-4">
                <h4 class="font-semibold text-gray-900">
                    {{ $title }}
                </h4>

                @if ($href)
                    <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                        Tersedia
                    </span>
                @else
                    <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600">
                        {{ $status }}
                    </span>
                @endif
            </div>

            <p class="mt-2 text-sm text-gray-500">
                {{ $description }}
            </p>
        </div>

        <div class="mt-5">
            @if ($href)
                <a
                    href="{{ $href }}"
                    class="inline-flex items-center text-sm font-medium text-green-700 hover:text-green-900"
                >
                    Buka Modul
                </a>
            @else
                <span class="text-xs font-medium text-gray-400">
                    Akan aktif setelah modul dibuat
                </span>
            @endif
        </div>
    </div>
</div>