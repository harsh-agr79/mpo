<div class="space-y-4">
    @foreach ($cartItems as $item)
        <div class="flex justify-between items-center border p-2 rounded">
            <div>
                <div class="font-medium">{{ $item['name'] }}</div>
                <div class="text-sm text-gray-600">रु{{ number_format($item['price'], 2) }}</div>
            </div>
            <div class="flex items-center gap-2">
                <input
                    type="number"
                    wire:model.lazy="quantities.{{ $item['id'] }}"
                    min="0"
                    class="w-16 border rounded px-2 py-1 text-sm"
                />
                <span class="font-medium">रु{{ number_format($item['subtotal'], 2) }}</span>
            </div>
        </div>
    @endforeach

    <div class="text-right text-lg font-semibold">
        Total: रु{{ number_format($total, 2) }}
    </div>
</div>
