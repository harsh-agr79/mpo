<x-filament-widgets::widget>
    <x-filament::section>
        <div x-data="{ open: false }">
            {{-- Always-visible info list --}}
            <x-filament::info-list :columns="2">
                <x-filament::info-list-item label="Name" :value="$order->user->name" />
                <x-filament::info-list-item label="Date" :value="$order->date" />
            </x-filament::info-list>

            {{-- Collapsible info list --}}
            <div x-show="open">
                <x-filament::info-list :columns="2" class="mt-2">
                    <x-filament::info-list-item label="Shop" :value="$order->user->shop_name" />
                    <x-filament::info-list-item label="Miti" :value="getNepaliDate($order->date)" />

                    <x-filament::info-list-item label="Phone no." :value="$order->user->contact" />
                    <x-filament::info-list-item label="Order ID" :value="$order->orderid" />

                    <x-filament::info-list-item label="Address" :value="$order->user->address" />
                    <x-filament::info-list-item label="Pan no." :value="$order->user->tax_no" />
                </x-filament::info-list>
            </div>

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
