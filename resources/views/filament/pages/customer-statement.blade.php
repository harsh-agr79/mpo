<x-filament-panels::page>
    <style>
        .amount-positive {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .dark .amount-positive {
            background-color: #4b1c1c;
            color: #f87171;
        }

        .amount-negative {
            background-color: #d1fae5;
            color: #065f46;
        }

        .dark .amount-negative {
            background-color: #064e3b;
            color: #6ee7b7;
        }

        .amount-cell {
            padding: 0.25rem 0.5rem;
            border: 1px solid #e5e7eb;
        }

        .dark .amount-cell {
            border-color: #374151;
        }
    </style>

    <div
        class="border bg-order-summary border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-900 text-black dark:text-white shadow-sm items-center">
        {{ $this->form }}
    </div>

    <div x-data="{ showVoucher: false, showRemarks: false, useNepaliDate: true }">
        <div
            class="flex flex-wrap gap-4 items-center bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <label class="inline-flex items-center gap-2 text-sm text-gray-900 dark:text-gray-100">
                <input type="checkbox" x-model="showVoucher"
                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:checked:bg-primary-500 text-primary-600 shadow-sm focus:ring-primary-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                <span>Voucher</span>
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-gray-900 dark:text-gray-100">
                <input type="checkbox" x-model="showRemarks"
                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:checked:bg-primary-500 text-primary-600 shadow-sm focus:ring-primary-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                <span>Remarks</span>
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-gray-900 dark:text-gray-100">
                <input type="checkbox" x-model="useNepaliDate"
                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:checked:bg-primary-500 text-primary-600 shadow-sm focus:ring-primary-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                <span>Nepali Date</span>
            </label>
        </div>

        @if (!empty($this->Data))
            <table class="table-auto w-full border mt-4 text-xs text-black dark:text-white">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="border px-2 py-1 dark:border-gray-700 cursor-pointer"
                            @click="$wire.call('sortBy', 'created')">
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
                        <template x-if="showVoucher">
                            <th class="border px-2 py-1 dark:border-gray-700">Voucher</th>
                        </template>
                        <template x-if="showRemarks">
                            <th class="border px-2 py-1 dark:border-gray-700">Remarks</th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    @php $amt = $this->openingBalance; @endphp
                    <tr class="bg-gray-50 dark:bg-gray-800 font-bold">
                        <td colspan="3" class="px-2 py-1 text-right">Opening Balance</td>
                        @if ($this->openingBalance > 0)
                            <td class="border px-2 py-1 dark:border-gray-700">
                                {{ number_format($this->openingBalance, 0) }}</td>
                            <td class="border px-2 py-1 dark:border-gray-700">0</td>
                        @else
                            <td class="border px-2 py-1 dark:border-gray-700">0</td>
                            <td class="border px-2 py-1 dark:border-gray-700">
                                {{ number_format(-1 * $this->openingBalance, 0) }}</td>
                        @endif
                        <td class="amount-cell {{ $this->openingBalance < 0 ? 'amount-negative' : 'amount-positive' }}">
                            {{ number_format(abs($this->openingBalance), 0) }}
                        </td>
                        <template x-if="showVoucher">
                            <td class="border px-2 py-1 dark:border-gray-700">-</td>
                        </template>
                        <template x-if="showRemarks">
                            <td class="border px-2 py-1 dark:border-gray-700">-</td>
                        </template>
                    </tr>

                    @foreach ($this->entries as $entry)
                        @php
                            $amt = $amt + $entry['debit'] - $entry['credit'];
                            $isNegative = $amt < 0;
                            $nepDate = getNepaliDate(\Carbon\Carbon::parse($entry['created'])->toDateString());
                            $engDate = \Carbon\Carbon::parse($entry['created'])->format('Y-m-d');
                        @endphp
                        <tr class="bg-white dark:bg-gray-900">
                            <td class="border px-2 py-1 dark:border-gray-700">
                                <span x-text="useNepaliDate ? '{{ $nepDate }}' : '{{ $engDate }}'"></span>
                            </td>
                            <td class="border px-2 py-1 dark:border-gray-700">{{ $entry['ent_id'] }}</td>
                            <td class="border px-2 py-1 dark:border-gray-700">{{ $entry['type'] }}</td>
                            <td class="border px-2 py-1 dark:border-gray-700">{{ number_format($entry['debit'], 0) }}
                            </td>
                            <td class="border px-2 py-1 dark:border-gray-700">{{ number_format($entry['credit'], 0) }}
                            </td>
                            <td class="amount-cell {{ $isNegative ? 'amount-negative' : 'amount-positive' }}">
                                {{ number_format(abs($amt), 0) }}
                            </td>
                            <template x-if="showVoucher">
                                <td class="border px-2 py-1 dark:border-gray-700">{{ $entry['voucher'] ?? '-' }}</td>
                            </template>
                            <template x-if="showRemarks">
                                <td class="border px-2 py-1 dark:border-gray-700">{{ $entry['remarks'] ?? '-' }}</td>
                            </template>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="font-bold text-sm bg-gray-100 dark:bg-gray-800">
                    @php
                        $totals = $this->Data['totals'];
                        $opening = $this->openingBalance;

                        $debit = $totals['sales'] + $totals['expenses'];
                        $credit = $totals['payments'] + $totals['returns'];

                        $net = $totals['netBalance'] + $opening;

                        $openingDebit = $opening > 0 ? $opening : 0;
                        $openingCredit = $opening < 0 ? abs($opening) : 0;

                        $debit += $openingDebit;
                        $credit += $openingCredit;
                    @endphp

                    @php
                        $colspan = 3;
                        if ($showVoucher) {
                            $colspan++;
                        }
                        if ($showRemarks) {
                            $colspan++;
                        }
                    @endphp

                    <tr>
                        <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                        <td class="border px-2 py-1 dark:border-gray-700">Opening Balance</td>
                        <td class="border px-2 py-1 dark:border-gray-700">
                            {{ $opening > 0 ? number_format($opening, 0) : '0' }}
                        </td>
                        <td class="border px-2 py-1 dark:border-gray-700">
                            {{ $opening < 0 ? number_format(abs($opening), 0) : '0' }}
                        </td>
                        <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @if ($showVoucher)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @endif
                        @if ($showRemarks)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @endif
                    </tr>

                    <tr>
                        <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                        <td class="border px-2 py-1 dark:border-gray-700">Total Sales</td>
                        <td class="border px-2 py-1 dark:border-gray-700">{{ number_format($totals['sales'], 0) }}</td>
                        <td class="border px-2 py-1 dark:border-gray-700"></td>
                        <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @if ($showVoucher)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @endif
                        @if ($showRemarks)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @endif
                    </tr>

                    <tr>
                        <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                        <td class="border px-2 py-1 dark:border-gray-700">Total Expenses</td>
                        <td class="border px-2 py-1 dark:border-gray-700">{{ number_format($totals['expenses'], 0) }}
                        </td>
                        <td class="border px-2 py-1 dark:border-gray-700"></td>
                        <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @if ($showVoucher)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @endif
                        @if ($showRemarks)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @endif
                    </tr>

                    <tr>
                        <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                        <td class="border px-2 py-1 dark:border-gray-700">Total Payments</td>
                        <td class="border px-2 py-1 dark:border-gray-700"></td>
                        <td class="border px-2 py-1 dark:border-gray-700">{{ number_format($totals['payments'], 0) }}
                        </td>
                        <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @if ($showVoucher)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @endif
                        @if ($showRemarks)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @endif
                    </tr>

                    <tr>
                        <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                        <td class="border px-2 py-1 dark:border-gray-700">Total Sales Returns</td>
                        <td class="border px-2 py-1 dark:border-gray-700"></td>
                        <td class="border px-2 py-1 dark:border-gray-700">{{ number_format($totals['returns'], 0) }}
                        </td>
                        <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @if ($showVoucher)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @endif
                        @if ($showRemarks)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @endif
                    </tr>

                    <tr>
                        <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                        <td class="border px-2 py-1 dark:border-gray-700">Total</td>
                        <td class="border px-2 py-1 dark:border-gray-700">{{ number_format($debit, 0) }}</td>
                        <td class="border px-2 py-1 dark:border-gray-700">{{ number_format($credit, 0) }}</td>
                        <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @if ($showVoucher)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @endif
                        @if ($showRemarks)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @endif
                    </tr>

                    <tr>
                        <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                        <td class="border px-2 py-1 dark:border-gray-700">Balance</td>
                        @if ($net > 0)
                            <td class="amount-cell amount-positive border dark:border-gray-700">
                                {{ number_format($net, 0) }}</td>
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @elseif ($net < 0)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                            <td class="amount-cell amount-negative border dark:border-gray-700">
                                {{ number_format(abs($net), 0) }}</td>
                        @else
                            <td class="border px-2 py-1 dark:border-gray-700">0</td>
                            <td class="border px-2 py-1 dark:border-gray-700">0</td>
                        @endif
                        <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @if ($showVoucher)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @endif
                        @if ($showRemarks)
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        @endif
                    </tr>
                </tfoot>


            </table>
        @endif
    </div>
</x-filament-panels::page>
