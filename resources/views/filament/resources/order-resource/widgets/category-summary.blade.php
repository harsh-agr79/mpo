<x-filament-widgets::widget>
    <x-filament::section>
        <style>
            .hide-scrollbar {
                scrollbar-width: none;
                /* Firefox */
                -ms-overflow-style: none;
                /* IE and Edge */
            }

            .hide-scrollbar::-webkit-scrollbar {
                display: none;
                /* Chrome, Safari, Opera */
            }
        </style>
        <div class="text-xs">




           
            <div
                class="mt-6 p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                <h3 class="text-base font-semibold text-black dark:text-white mb-3">Summary</h3>
                <div class="flex flex-col space-y-1 text-xs text-black dark:text-white whitespace-nowrap">
                    <div><span class="font-medium">Total Items:</span> {{ $totalItems }}</div>
                    <div><span class="font-medium">Total Approved Quantity:</span> {{ $totalApprovedQuantity }}</div>
                    <div><span class="font-medium">Total Approved Value:</span>
                        ₹{{ number_format($totalApprovedValue, 0) }}</div>
                    @if ($discount > 0)
                        <div><span class="font-medium text-red-600 dark:text-red-400">Discount:</span> -
                            {{ number_format($discount, 0) }}% ({{ number_format($totalApprovedValue - $finalTotal) }})
                        </div>
                        <div><span class="font-medium text-blue-600 dark:text-blue-400">Final Total:</span>
                            ₹{{ number_format($finalTotal, 0) }}</div>
                    @endif
                    @if ($totalBenefit > 0)
                        <div><span class="font-medium">Offered Benefit:</span> ₹{{ number_format($totalBenefit, 0) }}
                        </div>
                    @endif
                    @if ($totalApprovedValue - $finalTotal + $totalBenefit > 0)
                        <div class="text-green-600 dark:text-green-400">
                            <span class="font-medium">Net Benefit:</span>
                            ₹{{ number_format($totalApprovedValue - $finalTotal + $totalBenefit, 0) }}
                        </div>
                    @endif
                </div>
            </div>
             <div style="overflow-x: scroll;" class="hide-scrollbar">
                @if ($categoryCounts->isNotEmpty())
                    <table
                        class="w-full text-xs text-left text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded mt-6">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700">
                                <th class="px-4 py-2 border dark:border-gray-700 text-left">Metric</th>
                                @foreach ($categoryCounts as $category => $count)
                                    <th class="px-4 py-2 border dark:border-gray-700 text-center">{{ $category }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-4 py-2 border dark:border-gray-700 font-medium">Item Count</td>
                                @foreach ($categoryCounts as $count)
                                    <td class="px-4 py-2 border dark:border-gray-700 text-center">{{ $count }}
                                    </td>
                                @endforeach
                            </tr>
                            <tr class="bg-gray-50 dark:bg-gray-800">
                                <td
                                    class="px-4 py-2 border dark:border-gray-700 font-medium text-green-600 dark:text-green-400">
                                    Approved Quantity</td>
                                @foreach ($categoryApprovedSums as $sum)
                                    <td
                                        class="px-4 py-2 border dark:border-gray-700 text-center text-green-600 dark:text-green-400">
                                        {{ $sum }}</td>
                                @endforeach
                            </tr>
                            <tr class="bg-gray-100 dark:bg-gray-700">
                                <td
                                    class="px-4 py-2 border dark:border-gray-700 font-medium text-blue-600 dark:text-blue-400">
                                    Approved Value</td>
                                @foreach ($categoryApprovedValueSums as $value)
                                    <td
                                        class="px-4 py-2 border dark:border-gray-700 text-center text-blue-600 dark:text-blue-400">
                                        ₹{{ number_format($value, 0) }}
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                @else
                    <p class="mt-4 text-xs text-gray-600 dark:text-gray-400">No items found in this order.</p>
                @endif
            </div>
            <div
                class="mt-2 php artisan make:migration add_specifications_and_images_to_products_table
p-4 bg-gray-50 dark:bg-gray-800 border-l-4 border-blue-500 dark:border-blue-400 rounded shadow-sm">
                <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">User Remarks:</h3>
                <p class="text-xs text-black dark:text-white">
                    {{ $order->user_remarks ?? 'No remarks provided' }}
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
