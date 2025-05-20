<x-filament::page>
    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('message') }}
        </div>
    @endif
    <div class="bg-white items-center border rounded-lg p-3">
        {{ $this->form }}
        <div class="py-2 flex justify-between items-center">
            <div class="w-14">
                <x-filament::actions :actions="$this->getActions()" />
            </div>
            <div class="flex-1 px-2">
                <input type="text" id="productSearch" placeholder="Search products..."
                    class="w-full px-4 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
            </div>
        </div>
        <div class="text-center font-semibold text-sm py-1">
            Cart Total: रु{{ number_format($this->cartTotal, 2) }}
        </div>
    </div>
    <div style="height: 65vh; overflow-y:scroll;">
        <form wire:submit.prevent="checkout" class="space-y-4">
            @foreach ($this->Products as $product)
                <div class="product-card flex items-center gap-4 border rounded-lg p-4 bg-white shadow-sm w-full">
                    {{-- 📸 Smaller image (48 × 48 px on all screens) --}}
                    <div class="flex-shrink-0 w-12 overflow-hidden rounded border">
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                            style="height: 60px;" />
                    </div>

                    {{-- Details --}}
                    <div class="flex-1">
                        <h3 class="text-base font-medium product-name">{{ $product->name }}</h3>
                        <p class="text-xs text-gray-600 product-category">
                            {{ $product->category->name ?? 'N/A' }}
                        </p>

                        <div class="text-xs product-price bg-amber-700">रु{{ $product->price }}</div>
                    </div>

                    {{-- Quantity --}}
                    <div class="w-24">
                        <div class="text-xs">
                            <span class="{{ $product->in_stock ? 'text-green-600' : 'text-red-600' }} product-stock">
                                {{ $product->in_stock ? 'In Stock' : 'Out of Stock' }}
                            </span>
                        </div>
                        <input type="number" wire:model.lazy="quantities.{{ $product->id }}" min="0"
                            placeholder="Qty"
                            class="px-3 py-1 text-sm border border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                    </div>
                </div>
            @endforeach

            <div class="mt-6">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Checkout
                </button>
            </div>
        </form>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('productSearch');
            const productCards = document.querySelectorAll('.product-card');

            searchInput.addEventListener('input', () => {
                const query = searchInput.value.toLowerCase();

                productCards.forEach(card => {
                    const name = card.querySelector('.product-name')?.textContent.toLowerCase() ||
                        '';
                    const category = card.querySelector('.product-category')?.textContent
                        .toLowerCase() || '';
                    const price = card.querySelector('.product-price')?.textContent.toLowerCase() ||
                        '';
                    const stock = card.querySelector('.product-stock')?.textContent.toLowerCase() ||
                        '';

                    const matches = name.includes(query) || category.includes(query) || price
                        .includes(query) || stock.includes(query);

                    card.style.display = matches ? 'flex' : 'none';
                });
            });
        });
    </script>

</x-filament::page>
