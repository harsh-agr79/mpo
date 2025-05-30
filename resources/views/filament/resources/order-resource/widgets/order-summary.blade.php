<x-filament-widgets::widget>
    <x-filament::section>
        <div x-data="{ open: false }" class="text-xs">
            <!-- Grid container with consistent 2-column layout -->
            <div class="grid grid-cols-2 gap-2 w-full">
                <!-- Persistent fields (always visible) -->
                <div class="break-words">
                    <strong>Name:</strong> {{ $order->user->name }}
                </div>
                <div class="break-words">
                    <strong>Date:</strong> {{ $order->date }}
                </div>

                <!-- Collapsible fields (2-column layout) -->
                <template x-if="open">
                    <div class="break-words">
                        <strong>Shop:</strong> {{ $order->user->shop_name }}
                    </div>
                </template>
                <template x-if="open">
                    <div class="break-words">
                        <strong>Miti:</strong> {{ getNepaliDate($order->date) }}
                    </div>
                </template>

                <template x-if="open">
                    <div class="break-words">
                        <strong>Phone no.:</strong> {{ $order->user->contact }}
                    </div>
                </template>
                <template x-if="open">
                    <div class="break-words">
                        <strong>Order ID:</strong> {{ $order->orderid }}
                    </div>
                </template>

                <template x-if="open">
                    <div class="break-words">
                        <strong>Address:</strong> {{ $order->user->address }}
                    </div>
                </template>
                <template x-if="open">
                    <div class="break-words">
                        <strong>Pan no.:</strong> {{ $order->user->tax_no }}
                    </div>
                </template>

                <!-- Toggle button -->
                <div class="col-span-2 pt-1">
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