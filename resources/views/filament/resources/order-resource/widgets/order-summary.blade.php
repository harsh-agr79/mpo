<x-filament-widgets::widget>
    <x-filament::section>
        <div x-data="{ open: false }">
            {{-- Always-visible fields --}}
            {!! $infoList->columns(2)->toHtml() !!}

            {{-- Toggle Button --}}
            <div class="mt-2">
                <button @click="open = !open" class="text-blue-600 hover:underline text-sm">
                    <span x-show="!open">Show More</span>
                    <span x-show="open">Show Less</span>
                </button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
