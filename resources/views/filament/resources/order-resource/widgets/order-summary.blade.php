<x-filament-widgets::widget>
    <x-filament::section>
        <div x-data="{ open: false }" class="text-xs w-full">
            <table class="w-full table-auto text-center">
                <tbody>
                    {{-- Always Visible Row --}}
                    <tr>
                        <td class="font-semibold">Name:</td>
                        <td>{{ $order->user->name }}</td>
                    </tr>

                    {{-- Collapsible Rows --}}
                    <template x-if="open">
                        <tbody>
                            <tr>
                                <td class="font-semibold">Shop:</td>
                                <td>{{ $order->user->shop_name }}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold">Phone no.:</td>
                                <td>{{ $order->user->contact }}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold">Address:</td>
                                <td>{{ $order->user->address }}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold">Pan no.:</td>
                                <td>{{ $order->user->tax_no }}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold">Date:</td>
                                <td>{{ $order->date }}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold">Miti:</td>
                                <td>{{ getNepaliDate($order->date) }}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold">Order ID:</td>
                                <td>{{ $order->orderid }}</td>
                            </tr>
                        </tbody>
                    </template>
                </tbody>
            </table>

            <div class="text-center mt-2">
                <button 
                    class="text-blue-500 hover:underline text-sm"
                    @click="open = !open"
                >
                    <span x-text="open ? 'Show less' : 'Show more'"></span>
                </button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
