<x-filament-widgets::widget>
    <x-filament::section>
        <div x-data="{ open: false }" class="text-xs">
            <table class="w-full table-auto border-separate border-spacing-y-2">
                <tbody>
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>{{ $order->user->name }}</td>
                        <td><strong>Date:</strong></td>
                        <td>{{ $order->date }}</td>
                    </tr>
                    <template x-if="open">
                        <tr>
                            <td><strong>Shop:</strong></td>
                            <td>{{ $order->user->shop_name }}</td>
                            <td><strong>Miti:</strong></td>
                            <td>{{ getNepaliDate($order->date) }}</td>
                        </tr>
                    </template>
                    <template x-if="open">
                        <tr>
                            <td><strong>Phone no.:</strong></td>
                            <td>{{ $order->user->contact }}</td>
                            <td><strong>Order ID:</strong></td>
                            <td>{{ $order->orderid }}</td>
                        </tr>
                    </template>
                    <template x-if="open">
                        <tr>
                            <td><strong>Address:</strong></td>
                            <td>{{ $order->user->address }}</td>
                            <td><strong>Pan no.:</strong></td>
                            <td>{{ $order->user->tax_no }}</td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <button 
                class="mt-2 text-blue-500 hover:underline text-sm"
                @click="open = !open"
            >
                <span x-text="open ? 'Show less' : 'Show more'"></span>
            </button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
