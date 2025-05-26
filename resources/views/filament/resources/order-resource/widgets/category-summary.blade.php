<x-filament-widgets::widget>
    <x-filament::section>
       @if($categoryCounts->isNotEmpty())
            <table class="w-full text-sm text-left text-gray-700 border border-gray-200 rounded">
                <thead>
                    <tr class="bg-gray-100">
                        @foreach ($categoryCounts as $category => $count)
                            <th class="px-4 py-2 text-center border">{{ $category }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach ($categoryCounts as $category => $count)
                            <td class="px-4 py-2 border text-center">{{ $count }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        @else
            <p>No items found in this order.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
