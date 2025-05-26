<x-filament-widgets::widget>
    <x-filament::section>
    <div class="text-xs">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div class="space-y-2">
                <div><strong>Date:</strong> {{ $order->date }}</div>
                <div><strong>Miti:</strong> {{ getNepaliDate($order->date) }}</div>
                <div><strong>Shop:</strong> {{ $order->user->shop_name }}</div>
                <div><strong>Order ID:</strong> {{ $order->orderid }}</div>
            </div>
            <div class="space-y-2">
                <div><strong>Phone no.:</strong> {{ $order->user->contact }}</div>
                <div><strong>Address:</strong> {{ $order->user->address }}</div>
                <div><strong>Pan no.:</strong> {{ $order->user->tax_no }}</div>
            </div>
        </div>
    </div>
</x-filament::section>
</x-filament-widgets::widget>
