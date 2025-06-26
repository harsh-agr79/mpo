<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\SalesReturn;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;
use Filament\Forms\Form;
use Filament\Forms\Components\{Grid, Select, DatePicker};
// use Carbon\Carbon;


class CustomerStatement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.customer-statement';

   public ?int $customerId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public $selectedCustomerId;
    public $customers;
    public $customer;
    public $entries;
    public $openingBalance = 0;
    public $showVoucher = false;
    public $showRemarks = false;
    public $useNepaliDate = true;
           

    public string $sortField = 'created';
    public string $sortDirection = 'asc'; // default to descending

    public function getTitle(): string
    {
        return ''; // Ensure nothing is rendered
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(3)->schema([
                Select::make('customerId')
                    ->label('Select Customer')
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live(), // 👈 Live update

                DatePicker::make('startDate')
                    ->label('Start Date')
                    ->required()
                    ->default(getStartOfFiscalYear())
                    ->live(), // 👈 Live update

                DatePicker::make('endDate')
                    ->label('End Date')
                    ->required()
                    ->default(getEndOfFiscalYear())
                    ->live(), // 👈 Live update
            ]),
        ]);
    }

    public function updated($property)
    {
        if (in_array($property, ['customerId', 'startDate', 'endDate'])) {
            $this->customer = User::findOrFail($this->customerId);
            $this->getDataProperty(); // Refresh the statement
        }
    }

    public function mount(?int $customerId = null): void
    {
        $this->customers = User::orderBy('name')->get();
        $this->selectedCustomerId = $customerId ?? request()->get('customerId') ?? $this->customers->first()?->id;

        $this->customerId = $this->selectedCustomerId;
        $this->customer = User::findOrFail($this->customerId);

        $this->startDate = request()->get('startDate') ?? getStartOfFiscalYear();
        $this->endDate = request()->get('endDate') ?? getEndOfFiscalYear();

        $this->form->fill([
            'customerId' => $this->customerId,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }

    public function getCustomersProperty(): array
    {
        return $this->customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
            ];
        })->toArray();
    }

   public function getDataProperty()
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate)->addDay();

        // Opening balance before start date
        $ordersBefore = $this->customer->orders()
            ->where('mainstatus', 'approved')
            ->whereNull('deleted_at')
            ->whereDate('date', '<', $start)
            ->get();

        $paymentsBefore = $this->customer->payments()
            ->whereNull('deleted_at')
            ->whereDate('payment_date', '<', $start)
            ->get();

        $returnsBefore = $this->customer->salesReturns()
            ->whereDate('date', '<', $start)
            ->get();

        $expensesBefore = $this->customer->expenses()
            ->whereDate('expense_date', '<', $start)
            ->get();

        $openingDebit = $ordersBefore->sum('net_total') + $expensesBefore->sum('amount');
        $openingCredit = $paymentsBefore->sum('amount') + $returnsBefore->sum('net_total');

        $this->openingBalance = $openingDebit - $openingCredit;

        // Entries during selected date range
        $orders = $this->customer->orders()
            ->where('mainstatus', 'approved')
            ->whereNull('deleted_at')
            ->where('save', 0)
            ->whereBetween('date', [$start, $end])
            ->get();

        $payments = $this->customer->payments()
            ->whereNull('deleted_at')
            ->whereBetween('payment_date', [$start, $end])
            ->get();

        $returns = $this->customer->salesReturns()
            ->whereBetween('date', [$start, $end])
            ->get();

        $expenses = $this->customer->expenses()
            ->whereBetween('expense_date', [$start, $end])
            ->get();

        $entries = collect();

        $totalSales = 0;
        $totalPayments = 0;
        $totalReturns = 0;
        $totalExpenses = 0;

        foreach ($orders as $order) {
            $totalSales += $order->net_total;
            $entries->push([
                'created' => $order->date,
                'ent_id' => $order->orderid,
                'debit' => $order->net_total,
                'credit' => 0,
                'type' => 'Sale',
                'voucher' => '',
                'remarks' => '',
                'othersname' => $order->othersname,
            ]);
        }

        foreach ($payments as $payment) {
            $totalPayments += $payment->amount;
            $entries->push([
                'created' => $payment->payment_date,
                'ent_id' => $payment->id,
                'debit' => 0,
                'credit' => $payment->amount,
                'type' => 'Payment',
                'voucher' => $payment->voucher,
                'remarks' => $payment->remarks,
                'othersname' => '',
            ]);
        }

        foreach ($returns as $return) {
            $totalReturns += $return->net_total;
            $entries->push([
                'created' => $return->date,
                'ent_id' => $return->return_id,
                'debit' => 0,
                'credit' => $return->net_total,
                'type' => 'Sales Return',
                'voucher' => '',
                'remarks' => '',
                'othersname' => '',
            ]);
        }

        foreach ($expenses as $expense) {
            $totalExpenses += $expense->amount;
            $entries->push([
                'created' => $expense->expense_date,
                'ent_id' => $expense->id,
                'debit' => $expense->amount,
                'credit' => 0,
                'type' => 'Expense',
                'voucher' => $expense->particular,
                'remarks' => '',
                'othersname' => '',
            ]);
        }

        $this->entries = $entries->map(function ($item) {
            $item['created'] = Carbon::parse($item['created']);
            return $item;
        })->sortBy(function ($item) {
            return $item[$this->sortField];
        }, SORT_REGULAR, $this->sortDirection === 'desc')->values();

        $totalDebit = $this->entries->sum('debit');
        $totalCredit = $this->entries->sum('credit');
        $netBalance = $totalDebit - $totalCredit;

        return [
            'entries' => $this->entries,
            'customer' => $this->customer,
            'openingBalance' => $this->openingBalance,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totals' => [
                'sales' => $totalSales,
                'payments' => $totalPayments,
                'returns' => $totalReturns,
                'expenses' => $totalExpenses,
                'netBalance' => $netBalance,
            ],
        ];
    }

}
