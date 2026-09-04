@php
    $types = [
        'reel' => __('filament.reel'),
        'story' => __('filament.story'),
        'post' => __('filament.post'),
    ];
    $total = collect($counts)->sum();
@endphp
<div class="space-y-3">
    <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('filament.total_media') }}</span>
        <span class="text-lg font-semibold">{{ $total }}</span>
    </div>
    <div class="grid grid-cols-3 gap-3">
        @foreach ($types as $key => $label)
            <div class="rounded-lg border border-gray-200 dark:border-white/10 p-3 text-center">
                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $label }}</div>
                <div class="mt-1 text-2xl font-bold">{{ $counts[$key] ?? 0 }}</div>
            </div>
        @endforeach
    </div>
</div>
