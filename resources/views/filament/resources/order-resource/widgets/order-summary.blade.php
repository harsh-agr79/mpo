<x-filament-widgets::widget>
    <x-filament::section class="bg-status-approved">
        <div 
            x-data="{ open: false }" 
            @click="open = !open"
            class="text-xs w-full cursor-pointer"
        >
            <table class="w-full table-auto border-separate border-spacing-y-2">
                <tbody>
                    {{-- Always visible row --}}
                    <tr>
                        <td class="w-1/4"><strong>Name:</strong> {{ $order->user->name }}</td>
                        <td class="w-1/4"><strong>Date:</strong> {{ $order->date }}</td>
                    </tr>

                    {{-- Toggleable rows --}}
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
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
