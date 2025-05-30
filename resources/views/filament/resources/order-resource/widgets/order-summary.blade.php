<div x-data="{ open: true }" class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        {{-- Always visible --}}
        <div>
            {{ $infoList->only('user.name')->render() }}
        </div>
        <div>
            {{ $infoList->only('date')->render() }}
        </div>
    </div>

    <button x-on:click="open = !open" class="text-sm text-blue-600 hover:underline" type="button">
        <span x-text="open ? 'Hide details' : 'Show details'"></span>
    </button>

    <div x-show="open" x-transition>
        {{ $infoList->except(['user.name', 'date'])->render() }}
    </div>
</div>
