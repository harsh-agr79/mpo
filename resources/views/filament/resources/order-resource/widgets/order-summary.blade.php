<div x-data="{ open: true }" class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        {{-- Always visible fields --}}
        <div>
            {!! $infoList->render()->filter(fn($item) => in_array($item->getLabel(), ['Name'])) !!}
        </div>
        <div>
            {!! $infoList->render()->filter(fn($item) => in_array($item->getLabel(), ['Date'])) !!}
        </div>
    </div>

    {{-- Toggle button --}}
    <button
        x-on:click="open = !open"
        class="text-sm text-blue-600 hover:underline"
        type="button"
    >
        <span x-text="open ? 'Hide details' : 'Show details'"></span>
    </button>

    {{-- Collapsible fields --}}
    <div x-show="open" x-transition>
        {!! $infoList->render() !!}
    </div>
</div>
