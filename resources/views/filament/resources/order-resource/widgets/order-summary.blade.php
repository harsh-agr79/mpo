<x-filament-widgets::widget>
    <x-filament::section>
        <div x-data="{ open: false }" class="text-xs">
            <div class="grid grid-cols-2 gap-2">
                <!-- Always visible fields -->
                <div><strong>Name:</strong> {{ $order->user->name }}</div>
                <div><strong>Date:</strong> {{ $order->date }}</div>

                <!-- These stay in 2-column layout and collapse -->
                <div x-show="open"><strong>Shop:</strong> {{ $order->user->shop_name }}</div>
                <div x-show="open"><strong>Miti:</strong> {{ getNepaliDate($order->date) }}</div>

                <div x-show="open"><strong>Phone no.:</strong> {{ $order->user->contact }}</div>
                <div x-show="open"><strong>Order ID:</strong> {{ $order->orderid }}</div>

                <div x-show="open"><strong>Address:</strong> {{ $order->user->address }}</div>
                <div x-show="open"><strong>Pan no.:</strong> {{ $order->user->tax_no }}</div>

                <!-- Toggle button spans 2 columns -->
                <div class="col-span-2">
                    <button @click="open = !open"
                        class="text-blue-600 hover:underline focus:outline-none text-sm">
                        <span x-show="!open">Show More</span>
                        <span x-show="open">Show Less</span>
                    </button>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
