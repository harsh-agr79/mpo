<x-filament-widgets::widget>
    <x-filament::section>
        <div x-data="{ open: false }" class="text-xs w-full">
            <table class="w-full text-center table-auto border-collapse">
                <tbody>
                    {{-- Always visible row --}}
                    <tr>
                        <td class="w-1/2">
                            <div><strong>Name:</strong> {{ $order->user->name }}</div>
                        </td>
                        <td class="w-1/2">
                            <div><strong>Date:</strong> {{ $order->date }}</div>
                        </td>
                    </tr>

                    {{-- Collapsible rows --}}
                    <template x-if="open">
                        <template>
                            <tr>
                                <td><div><strong>Shop:</strong> {{ $order->user->shop_name }}</div></td>
                                <td><div><strong>Miti:</strong> {{ getNepaliDate($order->date) }}</div></td>
                            </tr>
                            <tr>
                                <td><div><strong>Phone no.:</strong> {{ $order->user->contact }}</div></td>
                                <td><div><strong>Order ID:</strong> {{ $order->orderid }}</div></td>
                            </tr>
                            <tr>
                                <td><div><strong>Address:</strong> {{ $order->user->address }}</div></td>
                                <td><div><strong>Pan no.:</strong> {{ $order->user->tax_no }}</div></td>
                            </tr>
                        </template>
                    </template>
                </tbody>
            </table>

            {{-- Toggle button --}}
            <div class="text-center mt-2">
                <button @click="open = !open" class="text-blue-500 hover:underline text-sm">
                    <span x-text="open ? 'Show less' : 'Show more'"></span>
                </button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
