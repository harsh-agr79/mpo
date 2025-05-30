<x-filament-widgets::widget>
    <x-filament::section>
        <div x-data="{ open: false }" class="text-xs w-full relative">
            <!-- Icon on the top-right corner -->
            <div class="absolute top-0 right-0 p-2">
                <button @click="open = !open" class="focus:outline-none">
                    <!-- Rotate icon when open -->
                    <svg 
                        x-bind:class="open ? 'transform rotate-180' : 'transform rotate-0'" 
                        class="w-4 h-4 transition-transform duration-300 text-gray-600" 
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <table class="w-full table-auto text-center border-separate border-spacing-y-2">
                <tbody>
                    <!-- Always visible row -->
                    <tr>
                        <td class="w-1/4"><strong>Name:</strong> {{ $order->user->name }}</td>
                        <td class="w-1/4"><strong>Date:</strong> {{ $order->date }}</td>
                    </tr>

                    <!-- Toggleable rows -->
                    <tr x-show="open">
                        <td><strong>Shop:</strong> {{ $order->user->shop_name }}</td>
                        <td><strong>Miti:</strong> {{ getNepaliDate($order->date) }}</td>
                    </tr>
                    <tr x-show="open">
                        <td><strong>Phone no.:</strong> {{ $order->user->contact }}</td>
                        <td><strong>Order ID:</strong> {{ $order->orderid }}</td>
                    </tr>
                    <tr x-show="open">
                        <td><strong>Address:</strong> {{ $order->user->address }}</td>
                        <td><strong>Pan no.:</strong> {{ $order->user->tax_no }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Existing text toggle (optional to keep) -->
            <div class="text-center mt-2">
                <button @click="open = !open" class="text-blue-500 hover:underline text-sm">
                    <span x-text="open ? 'Show less' : 'Show more'"></span>
                </button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
