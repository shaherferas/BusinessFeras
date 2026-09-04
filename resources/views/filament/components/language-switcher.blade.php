@php
    $currentLocale = session('filament_locale', 'en');
    $locales = ['en' => 'English', 'ar' => 'العربية'];
@endphp

<div class="fi-topbar-item" x-data="{ open: false }">
    <button
        @click="open = !open"
        @click.away="open = false"
        type="button"
        class="fi-topbar-item-btn flex items-center gap-x-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
        </svg>
        <span>{{ $locales[$currentLocale] ?? 'English' }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div
        x-show="open"
        x-transition
        class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800"
        style="display: none;"
    >
        <div class="py-1">
            @foreach ($locales as $code => $name)
                <form method="POST" action="{{ route('filament.admin.switch-locale') }}" class="inline">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $code }}">
                    <button
                        type="submit"
                        class="flex w-full items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700 {{ $currentLocale === $code ? 'font-semibold bg-gray-50 dark:bg-gray-900' : '' }}"
                    >
                        <span>{{ $name }}</span>
                        @if ($currentLocale === $code)
                            <svg class="h-4 w-4 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
    </div>
</div>
