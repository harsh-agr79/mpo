<x-filament::page>
    <style>
        .outofstock {
            color: red;
        }

        .instock {
            color: green;
        }
        .hide-scrollbar {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none;  /* IE and Edge */
        }

        .hide-scrollbar::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
        }
    </style>
    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('message') }}
        </div>
    @endif
    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-900 text-black dark:text-white shadow-sm items-center border rounded-lg p-3">
        {{ $this->form }}
        <div class="py-2 flex justify-between items-center">
            <div class="w-30">
                <x-filament::actions :actions="$this->getActions()" />
            </div>
            <div class="flex-1 px-2">
                <input type="text" id="productSearch"  style="color:black;" placeholder="Search products..."
                    class="w-full px-4 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
            </div>
        </div>
        <div class="text-center font-semibold text-sm py-1">
            {{-- Cart Total: रु{{ number_format($this->cartTotal, 2) }} --}}
        </div>
    </div>
    <div style="height: 65vh; overflow-y:scroll;" class="hide-scrollbar">
        <form wire:submit.prevent="checkout">
            @foreach ($this->Materials as $material)
               <div class="product-card flex items-center gap-4 border border-gray-200 dark:border-gray-700 rounded-lg p-2 bg-white dark:bg-gray-900 text-black dark:text-white shadow-sm w-full" style="margin-bottom:5px;">
                    <div class="flex overflow-hidden rounded border">
                        <img src="{{ $material->image && file_exists(storage_path('app/public/' . $material->image))
                            ? asset('storage/' . $material->image)
                            : asset('images/dummy.png') }}"
                            alt="{{ $material->name }}" style="width: 80px;" />
                    </div>

                    {{-- Details --}}
                    <div class="flex-1">
                        <p class="product-name" style="font-size: 13px; font-weight: 500;">{{ $material->name }}</p>
                        {{-- <p class="text-xs text-gray-600 product-category">
                            {{ $product->category->name ?? 'N/A' }}
                        </p> --}}

                        <div class="product-price">
                            {{-- <span style="background: rgb(255, 174, 0); color: white; font-size: 12px; font-weight: 500; padding: 2px; border-radius: 4px; margin: 1px;">रु{{ $product->price }}<span> --}}
                        </div>
                    </div>

                    {{-- Quantity --}}
                    <div class="w-20">
                        <div class="text-xs">
                            <span class="{{ $material->outofstock ? 'outofstock' : 'instock' }} product-stock">
                                {{ $material->outofstock ? 'Out of Stock' : 'In Stock' }}
                            </span>
                        </div>
                        <input type="number" wire:model.lazy="quantities.{{ $material->id }}" min="0"
                            placeholder="Qty"
                            class="w-full px-3 py-1 text-sm border border-gray-300 rounded-md text-black shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" style="color:black;" />
                    </div>
                </div>
            @endforeach
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
