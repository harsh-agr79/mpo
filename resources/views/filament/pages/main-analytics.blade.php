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
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    @endpush
    <div
        class="border bg-order-summary border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-900 text-black dark:text-white shadow-sm items-center">
        {{ $this->form }}
    </div>

    <div class="overflow-x-auto rounded-b-md">
        <div class="w-full divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900 p-3 rounded-md" id="accordion">
            @foreach ($this->getDataProperty()['categories'] as $index => $category)
                <div x-data="{ open: false }" class="bg-white dark:bg-gray-900">
                    <!-- Accordion Header -->
                    <button @click="open = !open"
                        class="w-full flex justify-between items-center px-4 py-3 text-sm sm:text-base text-left font-medium bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <div class="w-1/2 truncate" style="color: rgb(0, 162, 255)">{{ $category['category_name'] }}</div>
                        <div class="w-1/4 text-left">{{ $category['total_quantity'] }}</div>
                        <div class="w-1/4 text-right">{{ number_format($category['total_sales']) }}</div>
                    </button>

                    <!-- Accordion Content -->
                    <div x-show="open" x-collapse>
                        <table
                            class="w-full text-sm sm:text-base text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                            <thead class="bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-2 text-left">Product</th>
                                    <th class="px-4 py-2 text-left">Quantity</th>
                                    <th class="px-4 py-2 text-right">Sales (Rs.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($category['products'] as $product)
                                    <tr class="border-t border-gray-100 dark:border-gray-800">
                                        <td class="px-4 py-2">{{ $product['product_name'] }}</td>
                                        <td class="px-4 py-2 text-left">{{ $product['quantity'] }}</td>
                                        <td class="px-4 py-2 text-right">{{ number_format($product['sales']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-4 py-2 text-center text-gray-500 dark:text-gray-400"
                                            colspan="3">
                                            No products found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4 rounded-lg" wire:ignore>
            <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-100 mb-4">Category-wise Quantity</h2>
            <div id="quantityChart" style="width:100%; height:400px;"></div>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4 rounded-lg" wire:ignore>
            <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-100 mb-4">Category-wise Sales</h2>
            <div id="salesChart" style="width:100%; height:400px;"></div>
        </div>
    </div>


    @push('scripts')
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script>
            google.charts.load('current', {
                packages: ['corechart']
            });

            // Store latest data globally
            let latestData = @json($this->getDataProperty());

            google.charts.setOnLoadCallback(() => {
                drawCharts(latestData);
            });

            // Listen for Livewire events to redraw
            document.addEventListener('livewire:load', function() {
                Livewire.hook('message.processed', (message, component) => {
                    // Update latest data from Livewire
                    latestData = @this.get('dataProperty'); // <-- We need to get updated data here

                    // Instead, we’ll use a Livewire event with the updated data:

                    // So, wait for event below (see Step 3)
                });
            });

            // Listen for custom event to redraw charts with new data
            window.addEventListener('analyticsDataUpdated', event => {
                drawCharts(event.detail);
            });

            function drawCharts(data) {
                // console.log
                if (Array.isArray(data)) {
                    data = data[0];
                }
                const quantityData = [
                    ['Category', 'Quantity']
                ];
                const salesData = [
                    ['Category', 'Sales']
                ];

                data.categories.forEach(category => {
                    quantityData.push([category.category_name, category.total_quantity]);
                    salesData.push([category.category_name, category.total_sales]);
                });

                const options = {
                    legend: {
                        position: 'right',
                        alignment: 'center',
                        textStyle: {
                            fontSize: 14
                        }
                    },
                    chartArea: {
                        width: '70%',
                        height: '80%'
                    },
                    backgroundColor: 'transparent'
                };

                const quantityChart = new google.visualization.PieChart(document.getElementById('quantityChart'));
                quantityChart.draw(google.visualization.arrayToDataTable(quantityData), options);

                const salesChart = new google.visualization.PieChart(document.getElementById('salesChart'));
                salesChart.draw(google.visualization.arrayToDataTable(salesData), options);
            }
        </script>
    @endpush
    @php
        $aiContext = $this->getDataProperty();
    @endphp
     <script>
        window.__AI_PAGE_CONTEXT__ = @json($aiContext);

        window.addEventListener('context-updated', (e) => {
            window.__AI_PAGE_CONTEXT__ = e.detail.context;
        });
    </script>
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
                <input type="text" x-model="input" @keydown.enter="sendMessage()" placeholder="Type your message..."
                    class="chatbot-input" />
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
                    const data = document.getElementById('accordion');

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
                        `You are an intelligent assistant analyzing analytics of sales given in html format. Use the context below to answer the user's question.\n\nData:\n${data.outerHTML}\n\nUser Question: ${this.input}`;

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
