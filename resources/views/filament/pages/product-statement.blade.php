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
            background-color: #cc8b00;
            color: white;
            border: none;
            border-radius: 9999px;
            padding: 10px 20px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .chatbot-toggle-btn:hover {
            background-color: #af701e;
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
            overflow-x: hidden;
        }

        /* Scrollbar styling */
        .chatbot-messages::-webkit-scrollbar {
            width: 4px;
        }

        .chatbot-messages::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 3px;
        }

        /* Message wrapper alignment */
        .chatbot-msg-wrapper {
            display: flex;
            padding-bottom: 10px;
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
            padding: 8px 14px;
            border-radius: 16px;
            word-wrap: break-word;
            line-height: 1.4;
        }

        .chatbot-msg-user-bg {
            background-color: #c99300;
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
            animation: fadeScale 1s ease-in-out infinite alternate;
        }

        @keyframes fadeScale {
            0% {
                transform: scale(0.95);
                opacity: 0.6;
            }

            100% {
                transform: scale(1.1);
                opacity: 1;
            }
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
            color: black;
        }

        .chatbot-input:focus {
            border-color: #eba625;
        }

        /* Send button */
        .chatbot-send-btn {
            background-color: #ebb625;
            color: white;
            border: none;
            border-radius: 0 8px 8px 0;
            padding: 8px 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .chatbot-send-btn:hover {
            background-color: #af581e;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <div
        class="border bg-order-summary border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-900 text-black dark:text-white shadow-sm items-center">
        {{ $this->form }}
    </div>
    <div x-data="{ useNepaliDate: true }">
        <div
            class="flex flex-wrap gap-4 items-center bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <label class="inline-flex items-center gap-2 text-sm text-gray-900 dark:text-gray-100">
                <input type="checkbox" x-model="useNepaliDate"
                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:checked:bg-primary-500 text-primary-600 shadow-sm focus:ring-primary-500 focus:ring-offset-1 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                <span>Nepali Date</span>
            </label>
        </div>
        <div style="overflow-x: scroll;">
            @if (!empty($this->Data))
                <table class="table-auto w-full border mt-4 text-xs text-black dark:text-white">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="border px-2 py-1 dark:border-gray-700 cursor-pointer"
                                @click="$wire.call('sortBy', 'date')">
                                Date
                                @if ($sortField === 'date')
                                    @if ($sortDirection === 'asc')
                                        ▲
                                    @else
                                        ▼
                                    @endif
                                @endif
                            </th>
                            <th class="border px-2 py-1 dark:border-gray-700">Entry ID</th>
                            <th class="border px-2 py-1 dark:border-gray-700">Type</th>
                            <th class="border px-2 py-1 dark:border-gray-700">Out</th>
                            <th class="border px-2 py-1 dark:border-gray-700">In</th>
                            <th class="border px-2 py-1 dark:border-gray-700">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $amt = 0; @endphp
                        @foreach ($this->Data['entries'] as $entry)
                            @php
                                $amt = $amt + $entry['debit'] - $entry['credit'];
                                $isNegative = $amt < 0;
                                $nepDate = getNepaliDate(\Carbon\Carbon::parse($entry['date'])->toDateString());
                                $engDate = \Carbon\Carbon::parse($entry['date'])->format('Y-m-d');
                            @endphp
                            <tr class="bg-white dark:bg-gray-900">
                                <td class="border px-2 py-1 dark:border-gray-700">
                                    <span x-text="useNepaliDate ? '{{ $nepDate }}' : '{{ $engDate }}'"></span>
                                </td>
                                <td class="border px-2 py-1 dark:border-gray-700">{{ $entry['entry_id'] }}</td>
                                <td class="border px-2 py-1 dark:border-gray-700">{{ $entry['type'] }}</td>
                                <td class="border px-2 py-1 dark:border-gray-700">
                                    {{ number_format($entry['debit'], 0) }}
                                </td>
                                <td class="border px-2 py-1 dark:border-gray-700">
                                    {{ number_format($entry['credit'], 0) }}
                                </td>
                                <td class="amount-cell {{ $isNegative ? 'amount-negative' : 'amount-positive' }}">
                                    {{ number_format(abs($amt), 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="font-bold text-sm bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                            <td class="border px-2 py-1 dark:border-gray-700">Total Sales</td>
                            <td class="border px-2 py-1 dark:border-gray-700">
                                {{ number_format($this->Data['totalSales'], 0) }}
                            </td>
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                            <td class="border px-2 py-1 dark:border-gray-700">Total Decrease</td>
                            <td class="border px-2 py-1 dark:border-gray-700">
                                {{ number_format($this->Data['totalDecreased'], 0) }}
                            </td>
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        </tr>

                        <tr>
                            <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                            <td class="border px-2 py-1 dark:border-gray-700">Total Replaced</td>
                            <td class="border px-2 py-1 dark:border-gray-700">
                                {{ number_format($this->Data['totalReplaced'], 0) }}
                            </td>
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        </tr>
                        <tr>
                            <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                            <td class="border px-2 py-1 dark:border-gray-700">Total Purchase</td>
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                            <td class="border px-2 py-1 dark:border-gray-700">
                                {{ number_format($this->Data['totalPurchases'], 0) }}
                            </td>
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        </tr>

                        <tr>
                            <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                            <td class="border px-2 py-1 dark:border-gray-700">Total Returns</td>
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                            <td class="border px-2 py-1 dark:border-gray-700">
                                {{ number_format($this->Data['totalReturns'], 0) }}
                            </td>
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        </tr>

                        <tr>
                            <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                            <td class="border px-2 py-1 dark:border-gray-700">Total Increase</td>
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                            <td class="border px-2 py-1 dark:border-gray-700">
                                {{ number_format($this->Data['totalIncreased'], 0) }}
                            </td>
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        </tr>

                        <tr>
                            <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                            <td class="border px-2 py-1 dark:border-gray-700">Totals</td>
                            <td class="border px-2 py-1 dark:border-gray-700">
                                {{ number_format($this->Data['totalSales'] + $this->Data['totalDecreased'] + $this->Data['totalReplaced'], 0) }}
                            </td>
                            <td class="border px-2 py-1 dark:border-gray-700">
                                {{ number_format($this->Data['totalIncreased'] + $this->Data['totalPurchases'] + $this->Data['totalReturns'] + $this->Data['openingBalance'], 0) }}
                            </td>
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        </tr>

                        <tr>
                            <td class="border px-2 py-1 dark:border-gray-700" colspan="2"></td>
                            <td class="border px-2 py-1 dark:border-gray-700">Balance</td>
                            @if ($amt > 0)
                                <td class="amount-cell amount-positive border dark:border-gray-700">
                                    {{ number_format($amt, 0) }}</td>
                                <td class="border px-2 py-1 dark:border-gray-700"></td>
                            @elseif ($amt < 0)
                                <td class="border px-2 py-1 dark:border-gray-700"></td>
                                <td class="amount-cell amount-negative border dark:border-gray-700">
                                    {{ number_format(abs($amt), 0) }}</td>
                            @else
                                <td class="border px-2 py-1 dark:border-gray-700">0</td>
                                <td class="border px-2 py-1 dark:border-gray-700">0</td>
                            @endif
                            <td class="border px-2 py-1 dark:border-gray-700"></td>
                        </tr>


                    </tfoot>
                </table>
            @endif
        </div>

    </div>
    <div x-data="chatBot()" class="chatbot-container">
        <button @click="open = !open"
            class="chatbot-toggle group relative chatbot-toggle-btn text-white rounded-full p-4 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
            :class="{ 'rotate-180': open }">
            {{-- <svg x-show="!open" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M12 2C6.48 2 2 6.48 2 12c0 1.54.36 2.98.97 4.29L1 23l6.71-1.97C9.02 21.64 10.46 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm-1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
            </svg> --}}
            <span x-show="!open" style="font-weight: 600; color:rgb(255, 255, 255)">Ask AI</span>
            <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                </path>
            </svg>
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
                            <div class=""
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
                <button @click="sendMessage()" class="chatbot-send-btn">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M3.4 20.4l17.45-7.48c.81-.35.81-1.49 0-1.84L3.4 3.6c-.66-.29-1.39.2-1.39.91L2 9.12c0 .5.37.93.87.99L17 12 2.87 13.88c-.5.07-.87.49-.87.99l.01 4.61c0 .71.73 1.2 1.39.91z" />
                    </svg>
                </button>
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

                // getTransactionData() {
                //     const rows = document.querySelectorAll('table tbody tr');
                //     const table = document.querySelector('table');
                //     // console.log(table);
                //     return Array.from(rows).map(row => {
                //         const cells = row.querySelectorAll('td');

                //         if (cells.length < 6) return null;

                //         const date = cells[0]?.innerText.trim() || '';
                //         const entryId = cells[1]?.innerText.trim() || '';
                //         const type = cells[2]?.innerText.trim() || '';
                //         const debit = cells[3]?.innerText.replace(/,/g, '').trim() || '0';
                //         const credit = cells[4]?.innerText.replace(/,/g, '').trim() || '0';
                //         const balance = cells[5]?.innerText.replace(/,/g, '').trim() || '';

                //         const voucher = cells.length >= 7 ? cells[6]?.innerText.trim() : '';
                //         const remarks = cells.length >= 8 ? cells[7]?.innerText.trim() : '';

                //         // Filter out rows like "Total", "Opening Balance", etc.
                //         const skipKeywords = ['Opening Balance', 'Total', 'Balance', 'Total Sales',
                //             'Total Payments', 'Total Expenses', 'Total Sales Returns'
                //         ];
                //         if (skipKeywords.some(keyword => type.toLowerCase().includes(keyword.toLowerCase()))) {
                //             return null;
                //         }

                //         return {
                //             date,
                //             entryId,
                //             type,
                //             debit,
                //             credit,
                //             balance,
                //             voucher,
                //             remarks
                //         };
                //     }).filter(e => e !== null);
                // },

                // getTableSummary() {
                //     const rows = document.querySelectorAll('table tfoot tr');
                //     const summary = {};
                //     rows.forEach(row => {
                //         const cells = row.querySelectorAll('td');
                //         const label = cells[2]?.innerText.trim().toLowerCase();
                //         const debit = cells[3]?.innerText.replace(/,/g, '').trim() || '0';
                //         const credit = cells[4]?.innerText.replace(/,/g, '').trim() || '0';

                //         if (label.includes('opening balance')) {
                //             summary.openingBalance = {
                //                 debit: Number(debit),
                //                 credit: Number(credit)
                //             };
                //         } else if (label.includes('total sales')) {
                //             summary.totalSales = Number(debit);
                //         } else if (label.includes('total expenses')) {
                //             summary.totalExpenses = Number(debit);
                //         } else if (label.includes('total payments')) {
                //             summary.totalPayments = Number(credit);
                //         } else if (label.includes('total sales returns')) {
                //             summary.totalReturns = Number(credit);
                //         } else if (label === 'total') {
                //             summary.totalDebit = Number(debit);
                //             summary.totalCredit = Number(credit);
                //         } else if (label === 'balance') {
                //             summary.balance = {
                //                 debit: Number(debit) || 0,
                //                 credit: Number(credit) || 0
                //             };
                //         }
                //     });
                //     return summary;
                // },

                async sendMessage() {
                    if (!this.input.trim()) return;

                    // const transactions = this.getTransactionData();
                    // const summary = this.getTableSummary();
                    const table = document.querySelector('table');

                    // const entriesSummary = transactions.map(e =>
                    //     `Date: ${e.date}\nEntry ID: ${e.entryId}\nType: ${e.type}\nDebit: ${e.debit}\nCredit: ${e.credit}\nBalance: ${e.balance}\nVoucher: ${e.voucher}\nRemarks: ${e.remarks}\n---`
                    // ).join('\n');

                    // const contextPrompt =
                    //     `Opening Balance: Debit ${summary.openingBalance?.debit || 0}, Credit ${summary.openingBalance?.credit || 0}\n` +
                    //     `Total Sales: ${summary.totalSales || 0}\nTotal Expenses: ${summary.totalExpenses || 0}\n` +
                    //     `Total Payments: ${summary.totalPayments || 0}\nTotal Sales Returns: ${summary.totalReturns || 0}\n` +
                    //     `Overall Total Debit: ${summary.totalDebit || 0}, Total Credit: ${summary.totalCredit || 0}\n` +
                    //     `Net Balance: Debit ${summary.balance?.debit || 0}, Credit ${summary.balance?.credit || 0}\n\n` +
                    //     `Transactions:\n${entriesSummary}`;

                    const fullPrompt =
                        `You are an intelligent assistant analyzing a Products quantity statement given in html table format (In pieces). Use the context below to answer the user's question.\n\nTable:\n${table.outerHTML}\n\nUser Question: ${this.input}`;

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
                            const container = document.querySelector('[x-data="chatBot()"] .chatbot-messages');
                            container.scrollTop = container.scrollHeight;
                        });
                    }
                }
            };
        }
    </script>
</x-filament-panels::page>
