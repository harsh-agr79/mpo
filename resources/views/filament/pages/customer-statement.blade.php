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

        /* Container positioning */
        .chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            max-width: 360px;
            max-height: 600px;
            z-index: 9999;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Toggle button */
        .chatbot-toggle-btn {
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 9999px;
            padding: 10px 20px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease;
        }

        .chatbot-toggle-btn:hover {
            background-color: #1e40af;
        }

        /* Chat window */
        .chatbot-window {
            margin-top: 10px;
            background-color: white;
            border: 1px solid #d1d5db;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            padding: 16px;
            display: flex;
            flex-direction: column;
            max-height: 500px;
            width: 100%;
        }

        /* Messages area */
        .chatbot-messages {
            flex: 1;
            overflow-y: auto;
            padding-right: 6px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        /* Scrollbar styling */
        .chatbot-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chatbot-messages::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 3px;
        }

        /* Message wrapper alignment */
        .chatbot-msg-wrapper {
            display: flex;
        }

        .chatbot-msg-user {
            justify-content: flex-end;
        }

        .chatbot-msg-bot {
            justify-content: flex-start;
        }

        /* Message bubbles */
        .chatbot-msg {
            max-width: 80%;
            padding: 10px 16px;
            border-radius: 16px;
            white-space: pre-wrap;
            word-wrap: break-word;
            line-height: 1.4;
        }

        .chatbot-msg-user-bg {
            background-color: #2563eb;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .chatbot-msg-bot-bg {
            background-color: #f3f4f6;
            color: black;
            border-bottom-left-radius: 4px;
        }

        /* Loading indicator */
        .chatbot-loading {
            text-align: center;
            color: gray;
            font-size: 12px;
            padding: 8px;
        }

        /* Input section */
        .chatbot-input-wrapper {
            display: flex;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            gap: 0;
        }

        /* Input box */
        .chatbot-input {
            flex: 1;
            border: 1px solid #d1d5db;
            border-radius: 8px 0 0 8px;
            padding: 8px 12px;
            outline: none;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .chatbot-input:focus {
            border-color: #2563eb;
        }

        /* Send button */
        .chatbot-send-btn {
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 0 8px 8px 0;
            padding: 8px 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .chatbot-send-btn:hover {
            background-color: #1e40af;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
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

    @php
        $aiContext = [
            'customer' => [
                'name' => $this->customer->name,
                'id' => $this->customer->id,
            ],
            'openingBalance' => $this->openingBalance,
            'totals' => $this->Data['totals'],
            'entries' => $this->entries->map(function ($entry) {
                return [
                    'date' => $entry['created']->toDateString(),
                    'type' => $entry['type'],
                    'debit' => $entry['debit'],
                    'credit' => $entry['credit'],
                    'remarks' => $entry['remarks'],
                    'othersname' => $entry['othersname'],
                ];
            }),
        ];
    @endphp

    <script>
        window.__AI_PAGE_CONTEXT__ = @json($aiContext);
    </script>
    <div x-data="chatBot()" class="chatbot-container">
        <button @click="open = !open" class="chatbot-toggle-btn">
            <span x-text="open ? 'Close Chat' : 'Ask AI'"></span>
        </button>

        <div x-show="open" x-transition class="chatbot-window">
            <div class="chatbot-messages">
                <template x-for="message in messages" :key="message.id">
                    <div :class="{
                        'chatbot-msg-user': message.role === 'user',
                        'chatbot-msg-bot': message
                            .role === 'bot'
                    }"
                        class="chatbot-msg-wrapper">
                        <div class="chatbot-msg"
                            :class="message.role === 'user' ? 'chatbot-msg-user-bg' : 'chatbot-msg-bot-bg'">
                            <div class="chatbot-msg" 
                                :class="message.role === 'user' ? 'chatbot-msg-user-bg' : 'chatbot-msg-bot-bg'"
                                x-html="marked.parse(message.text)">
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="loading" class="chatbot-loading">AI is typing...</div>
            </div>

            <div class="chatbot-input-wrapper">
                <input type="text" x-model="input" @keydown.enter="sendMessage()"
                    placeholder="Type your message..." class="chatbot-input" />
                <button @click="sendMessage()" class="chatbot-send-btn">Send</button>
            </div>
        </div>
    </div>


    <script>
        function chatBot() {
            return {
                open: false,
                input: '',
                messages: [],
                loading: false,

                async sendMessage() {
                    if (!this.input.trim()) return;

                    const context = window.__AI_PAGE_CONTEXT__;
                    const entriesSummary = context.entries.map(e =>
                        `Date: ${e.date}\nType: ${e.type}\nDebit: ${e.debit}\nCredit: ${e.credit}\nRemarks: ${e.remarks}\nOthers: ${e.othersname}\n---`
                    ).join('\n');
                    const contextPrompt =
                        `Customer Name: ${context.customer.name}\nOpening Balance: ${context.openingBalance}\nTotals: ${JSON.stringify(context.totals)}\n\nTransactions:\n${entriesSummary}`;
                    const fullPrompt =
                        `You are an intelligent assistant analyzing a customer financial statement. Use the context below to answer the user's question.\n\nContext:\n${contextPrompt}\n\nUser Question: ${this.input}`;

                    const userMsg = {
                        id: Date.now(),
                        role: 'user',
                        text: this.input
                    };
                    this.messages.push(userMsg);
                    this.loading = true;
                    this.input = '';

                    const botId = Date.now() + 1;
                    this.messages.push({
                        id: botId,
                        role: 'bot',
                        text: '...'
                    });

                    try {
                        const response = await fetch('/ai-chat', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                prompt: fullPrompt
                            })
                        });

                        const data = await response.json();
                        const reply = data.reply || 'No response received.';

                        this.messages = this.messages.map(m =>
                            m.id === botId ? {
                                ...m,
                                text: reply
                            } : m
                        );
                    } catch (error) {
                        this.messages.push({
                            id: Date.now() + 2,
                            role: 'bot',
                            text: 'An error occurred. Please try again.'
                        });
                    } finally {
                        this.loading = false;
                        this.$nextTick(() => {
                            const container = document.querySelector('[x-data=\"chatBot()\"] .overflow-y-auto');
                            container.scrollTop = container.scrollHeight;
                        });
                    }
                }
            };
        }
    </script>




</x-filament-panels::page>
