<x-filament-widgets::widget>
    <x-filament::section>
        <div x-data="{ open: false }" class="text-xs">
            <div class="grid grid-cols-2 gap-2">
                <!-- Always visible top row -->
                <div>
                    <div><strong>Name:</strong> {{ $order->user->name }}</div>
                </div>
                <div>
                    <div><strong>Date:</strong> {{ $order->date }}</div>
                </div>

                <!-- Collapsible section -->
                <template x-if="open">
                    <div class="col-span-2 grid grid-cols-2 gap-2">
                        <div class="space-y-2">
                            <div><strong>Shop:</strong> {{ $order->user->shop_name }}</div>
                            <div><strong>Phone no.:</strong> {{ $order->user->contact }}</div>
                            <div><strong>Address:</strong> {{ $order->user->address }}</div>
                            <div><strong>Pan no.:</strong> {{ $order->user->tax_no }}</div>
                        </div>
                        <div class="space-y-2">
                            <div><strong>Miti:</strong> {{ getNepaliDate($order->date) }}</div>
                            <div><strong>Order ID:</strong> {{ $order->orderid }}</div>
                        </div>
                    </div>
                </template>

                <!-- Toggle button -->
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
