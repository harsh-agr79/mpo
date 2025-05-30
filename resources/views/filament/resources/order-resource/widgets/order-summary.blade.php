<x-filament-widgets::widget>
    <x-filament::section>
        <div x-data="{ open: false }" class="text-xs w-full">
            <table class="w-full table-auto text-center border-separate border-spacing-y-2">
                <tbody>
                    {{-- Always visible row --}}
                    <tr>
                        <td class="w-1/4"><strong>Name:</strong></td>
                        <td class="w-1/4">{{ $order->user->name }}</td>
                        <td class="w-1/4"><strong>Date:</strong></td>
                        <td class="w-1/4">{{ $order->date }}</td>
                    </tr>

                    {{-- Toggleable rows --}}
                    <tr x-show="open">
                        <td><strong>Shop:</strong></td>
                        <td>{{ $order->user->shop_name }}</td>
                        <td><strong>Miti:</strong></td>
                        <td>{{ getNepaliDate($order->date) }}</td>
                    </tr>
                    <tr x-show="open">
                        <td><strong>Phone no.:</strong></td>
                        <td>{{ $order->user->contact }}</td>
                        <td><strong>Order ID:</strong></td>
                        <td>{{ $order->orderid }}</td>
                    </tr>
                    <tr x-show="open">
                        <td><strong>Address:</strong></td>
                        <td>{{ $order->user->address }}</td>
                        <td><strong>Pan no.:</strong></td>
                        <td>{{ $order->user->tax_no }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="text-center mt-2">
                <button 
                    @click="open = !open"
                    class="text-blue-500 hover:underline text-sm"
                >
                    <span x-text="open ? 'Show less' : 'Show more'"></span>
                </button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
