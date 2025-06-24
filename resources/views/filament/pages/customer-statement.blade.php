<x-filament-panels::page>
    <style>
    .amount-positive {
        background-color: #fee2e2; /* light red */
        color: #991b1b; /* dark red */
    }

    .amount-negative {
        background-color: #d1fae5; /* light green */
        color: #065f46; /* dark green */
    }

    .amount-cell {
        padding: 0.25rem 0.5rem;
        border: 1px solid #e5e7eb;
    }
</style>
    <div class="mb-4">
        <form method="GET">
            <label for="customer">Select Customer:</label>
            <select name="customerId" id="customer" onchange="this.form.submit()" class="border rounded px-2 py-1">
                @foreach ($this->Customers as $cust)
                    <option value="{{ $cust['id'] }}" {{ $cust['id'] == $selectedCustomerId ? 'selected' : '' }}>
                        {{ $cust['name'] }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
    @if(!empty($this->Data))
    <div class="mb-4">
        <strong>Statement for:</strong> {{ $this->Data['customer']['name'] }}<br>
        <strong>Period:</strong> {{ $this->startDate }} to {{ $this->endDate }}<br>
        <strong>Opening Balance:</strong> {{ number_format($this->openingBalance, 0) }}
    </div>

    <table class="table-auto w-full border mt-4 text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th class="cursor-pointer border px-2 py-1" wire:click="sortBy('created')">
                    Date
                    @if ($sortField === 'created')
                        @if ($sortDirection === 'asc')
                            ▲
                        @else
                            ▼
                        @endif
                    @endif
                </th>
                <th class="border px-2 py-1">Entry ID</th>
                <th class="border px-2 py-1">Type</th>
                <th class="border px-2 py-1">Debit</th>
                <th class="border px-2 py-1">Credit</th>
                {{-- <th class="border px-2 py-1">Remarks</th> --}}
                <th class="border px-2 py-1">Running Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-gray-50 font-bold">
                <td colspan="3" class="px-2 py-1 text-right">Opening Balance</td>
                @if($this->openingBalance > 0)
                <td class="border px-2 py-1">{{ number_format($this->openingBalance, 0) }}</td>
                <td class="border px-2 py-1">0</td>
                @else
                <td class="border px-2 py-1">0</td>
                <td class="border px-2 py-1">{{ number_format(-1*$this->openingBalance, 0) }}</td>
                @endif
            </tr>
            @php
                $amt = $this->openingBalance;
            @endphp
            @foreach ($this->entries as $entry)
                <tr>
                    <td class="border px-2 py-1">{{ \Carbon\Carbon::parse($entry['created'])->toDateString() }}</td>
                    <td class="border px-2 py-1">{{ $entry['ent_id'] }}</td>
                    <td class="border px-2 py-1">{{ $entry['type'] }}</td>
                    <td class="border px-2 py-1">{{ number_format($entry['debit'], 0) }}</td>
                    <td class="border px-2 py-1">{{ number_format($entry['credit'], 0) }}</td>
                    {{-- <td class="border px-2 py-1">{{ $entry['remarks'] ?? '' }}</td> --}}
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
