<x-filament-panels::page>
    {{-- {{ dd($this->getDataProperty()) }} --}}
    {{-- Filter Form --}}
    <div
        class="border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 rounded-lg p-4 shadow-sm text-black dark:text-white">
        {{ $this->form }}
    </div>

    {{-- Product Checkboxes (if category is selected) --}}
    {{-- @if ($categoryId)
        <div class="mt-4 bg-white dark:bg-gray-900 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="font-semibold mb-2">Select Products for Line Chart:</p>
            @php
                $selectedCategory = \App\Models\Category::with('products')->find($categoryId);
            @endphp
            @if ($selectedCategory && $selectedCategory->products->count())
                <div class="flex flex-wrap gap-4">
                    @foreach ($selectedCategory->products as $product)
                        <label class="inline-flex items-center space-x-2">
                            <input type="checkbox" value="{{ $product->name }}" class="product-toggle" checked>
                            <span>{{ $product->name }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
    @endif --}}



    {{-- Data Table --}}
    <div class="mt-6 overflow-x-auto">
        <table class="table-auto w-full border text-sm text-gray-700 dark:text-gray-200">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-2 text-left">Month</th>
                    @foreach ($this->getDataProperty()['categories'] as $category)
                        <th class="px-4 py-2 text-center">
                            @if ($categoryId)
                                <label class="inline-flex items-center space-x-2">
                                    <input type="checkbox" value="{{ $category }}" class="product-toggle">
                                    <span>{{ $category }}</span>
                                </label> @else{{ $category }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($this->getDataProperty()['data'] as $month => $values)
                    <tr class="border-t border-gray-200 dark:border-gray-700">
                        <td class="px-4 py-2">{{ $month }}</td>
                        @foreach ($this->getDataProperty()['categories'] as $category)
                            <td class="px-4 py-2 text-center">
                                {{ $values[$category] ?? 0 }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="bg-white border border-gray-200 dark:border-gray-700 p-4 rounded-lg" wire:ignore>
        {{-- <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-100 mb-4">Line Chart</h2> --}}
        <div id="lineChart" style="width:100%;"></div>
    </div>
    @push('scripts')
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script>
            google.charts.load('current', {
                packages: ['line']
            });

            let chartData = @json($this->getDataProperty());

            document.addEventListener('DOMContentLoaded', function() {
                google.charts.setOnLoadCallback(() => drawChart(chartData));

                Livewire.hook('message.processed', () => {
                    chartData = @json($this->getDataProperty());
                    drawChart(chartData);
                });

                document.addEventListener('change', function(e) {
                    if (e.target.classList.contains('product-toggle')) {
                        drawChart(chartData);
                    }
                });

                window.addEventListener('analyticsDataUpdated', event => {
                    chartData = event.detail;
                    drawChart(chartData);
                });
            });

            function getSelectedItems(dataSet) {
                if (dataSet.categoryId === null) {
                    return dataSet.categories;
                }
                return Array.from(document.querySelectorAll('.product-toggle:checked')).map(cb => cb.value);
            }

            function drawChart(dataSet) {
                if (Array.isArray(dataSet)) {
                    dataSet = dataSet[0];
                }

                const container = document.getElementById('lineChart');
                if (!container || !google.visualization) return;

                // 🧹 Ensure chart resets if structure changes
                container.innerHTML = '';

                const data = new google.visualization.DataTable();
                data.addColumn('string', 'Month');

                const categories = getSelectedItems(dataSet);
                if (!categories || categories.length === 0) return;

                categories.forEach(cat => {
                    try {
                        data.addColumn('number', cat);
                    } catch (e) {
                        console.warn('Failed to add column', cat, e);
                    }
                });

                const months = Object.keys(dataSet.data || {});
                months.forEach(month => {
                    const row = [month];
                    categories.forEach(cat => {
                        row.push(dataSet.data[month]?.[cat] ?? 0);
                    });
                    data.addRow(row);
                });

                const options = {
                    chart: {
                        title: 'Approved Quantity Trend',
                        subtitle: 'Nepali Month-wise'
                    },
                    width: container.offsetWidth,
                    height: 500
                };

                const chart = new google.charts.Line(container);
                chart.draw(data, google.charts.Line.convertOptions(options));
            }
        </script>
    @endpush



</x-filament-panels::page>
