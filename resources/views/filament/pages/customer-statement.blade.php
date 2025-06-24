<x-filament-panels::page>
    <style>
        .amount-positive {
            background-color: #fee2e2; /* light red */
            color: #991b1b; /* dark red */
        }

        .dark .amount-positive {
            background-color: #4b1c1c; /* darker red for dark mode */
            color: #f87171; /* light red text */
        }

        .amount-negative {
            background-color: #d1fae5; /* light green */
            color: #065f46; /* dark green */
        }

        .dark .amount-negative {
            background-color: #064e3b; /* dark green bg for dark mode */
            color: #6ee7b7; /* light green text */
        }

        .amount-cell {
            padding: 0.25rem 0.5rem;
            border: 1px solid #e5e7eb;
        }

        .dark .amount-cell {
            border-color: #374151; /* darker border for dark mode */
        }
    </style>

    <div class="border bg-order-summary border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-900 text-black dark:text-white shadow-sm items-center">
        {{ $this->form }}
    </div>

    @if(!empty($this->Data))
    <table class="table-auto w-full border mt-4 text-xs text-black dark:text-white">
        <thead class="bg-gray-100 dark:bg-gray-800">
            <tr>
                <th class="cursor-pointer border px-2 py-1 dark:border-gray-700" wire:click="sortBy('created')">
                    Date
                    @if ($sortField === 'created')
                        @if ($sortDirection === 'asc')
                            ▲
                        @else
                            ▼
                        @endif
                    @endif
                </th>
                <th class="border px-2 py-1 dark:border-gray-700">Entry ID</th>
                <th class="border px-2 py-1 dark:border-gray-700">Type</th>
                <th class="border px-2 py-1 dark:border-gray-700">Debit</th>
                <th class="border px-2 py-1 dark:border-gray-700">Credit</th>
                <th class="border px-2 py-1 dark:border-gray-700">Running Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-gray-50 dark:bg-gray-800 font-bold">
                <td colspan="3" class="px-2 py-1 text-right">Opening Balance</td>
                @if($this->openingBalance > 0)
                <td class="border px-2 py-1 dark:border-gray-700">{{ number_format($this->openingBalance, 0) }}</td>
                <td class="border px-2 py-1 dark:border-gray-700">0</td>
                @else
                <td class="border px-2 py-1 dark:border-gray-700">0</td>
                <td class="border px-2 py-1 dark:border-gray-700">{{ number_format(-1*$this->openingBalance, 0) }}</td>
                @endif
            </tr>
            @php
                $amt = $this->openingBalance;
            @endphp
            @foreach ($this->entries as $entry)
                <tr class="bg-white dark:bg-gray-900">
                    <td class="border px-2 py-1 dark:border-gray-700">{{ getNepaliDate(\Carbon\Carbon::parse($entry['created'])->toDateString()) }}</td>
                    <td class="border px-2 py-1 dark:border-gray-700">{{ $entry['ent_id'] }}</td>
                    <td class="border px-2 py-1 dark:border-gray-700">{{ $entry['type'] }}</td>
                    <td class="border px-2 py-1 dark:border-gray-700">{{ number_format($entry['debit'], 0) }}</td>
                    <td class="border px-2 py-1 dark:border-gray-700">{{ number_format($entry['credit'], 0) }}</td>
                    @php
                        $amt = $amt + $entry['debit'] - $entry['credit'];
                        $isNegative = $amt < 0;
                    @endphp
                    <td class="amount-cell {{ $isNegative ? 'amount-negative' : 'amount-positive' }}">
                        {{ number_format(abs($amt), 0) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</x-filament-panels::page>
