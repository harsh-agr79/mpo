<?php

namespace App\Filament\Pages;

use App\Models\Product;
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
use Filament\Forms\Components\ {
    Grid, Select, DatePicker}
    ;
    // use Carbon\Carbon;

    class ProductStatement extends Page {
        protected static ?string $navigationIcon = 'heroicon-o-credit-card';

        protected static ?string $navigationGroup = 'Analytics';

        protected static string $view = 'filament.pages.product-statement';

        public ?int $productId = null;
        public ?string $startDate = null;
        public ?string $endDate = null;
        public $selectedProductId;
        public $products;
        public $product;
        public $entries;
        public $openingBalance = 0;
        public $useNepaliDate = true;

        public string $sortField = 'date';
        public string $sortDirection = 'asc';
        // default to descending

        public function getTitle(): string {
            return '';
            // Ensure nothing is rendered
        }

        public function sortBy( $field ) {
            if ( $this->sortField === $field ) {
                $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                $this->sortField = $field;
                $this->sortDirection = 'asc';
            }
        }

        public function form( Form $form ): Form {
            return $form->schema( [
                Grid::make( 3 )->schema( [
                    Select::make( 'productId' )
                    ->label( 'Select Product' )
                    ->options( Product::pluck( 'name', 'id' ) )
                    ->searchable()
                    ->required()
                    ->live(), // 👈 Live update

                    DatePicker::make( 'startDate' )
                    ->label( 'Start Date' )
                    ->required()
                    ->default( getStartOfFiscalYear() )
                    ->live(), // 👈 Live update

                    DatePicker::make( 'endDate' )
                    ->label( 'End Date' )
                    ->required()
                    ->default( getEndOfFiscalYear() )
                    ->live(), // 👈 Live update
                ] ),
            ] );
        }

        public function updated( $property ) {
            if ( in_array( $property, [ 'productId', 'startDate', 'endDate' ] ) ) {
                $this->product = Product::findOrFail( $this->productId );
                $this->getDataProperty();
                // Refresh the statement
            }
        }

        public function mount( ?int $productId = null ): void {
            $this->products = Product::orderBy( 'name' )->get();
            $this->selectedProductId = $productId ?? request()->get( 'productId' ) ?? $this->products->first()?->id;

            $this->productId = $this->selectedProductId;
            $this->product = Product::findOrFail( $this->productId );

            $this->startDate = request()->get( 'startDate' ) ?? getStartOfFiscalYear();
            $this->endDate = request()->get( 'endDate' ) ?? getEndOfFiscalYear();

            $this->form->fill( [
                'productId' => $this->productId,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
            ] );
        }

        public function getProductsProperty(): array {
            return $this->products->map( function ( $product ) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                ];
            }
        )->toArray();
    }

    public function getDataProperty()
    {
        $product = $this->product;

        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate)->endOfDay();
        $baseDate = Carbon::parse($product->open_stock_date ?? '2000-01-01');

        $openingBalance = $product->open_stock_count;
        $entries = collect();

        

        // If base date is before start, calculate adjustment-based opening balance
        if ($baseDate->lt($start)) {
            $purchases = $product->totalPurchasedQuantityBetween($baseDate, $start);
            $sales = $product->approvedQuantityBetween($baseDate, $start);
            $returns = $product->totalSalesReturnQuantityBetween($baseDate, $start);
            $increased = $product->totalIncreasedQuantityBetween($baseDate, $start);
            $decreased = $product->totalDecreasedQuantityBetween($baseDate, $start);
            $replaced = $product->totalDamageReplacedWithOtherBetween($baseDate, $start);

            $openingBalance += ($purchases + $returns + $increased) - ($sales + $decreased + $replaced);
        }

        // Add opening balance entry
        $entries->push([
            'date' => $start->copy()->startOfDay()->toDateString(),
            'entry_id' => null, // No entry ID for opening balance
            'type' => 'Opening Balance',
            'debit' => 0,
            'credit' => $openingBalance,
        ]);

        $pur = 0;
        $sal = 0;
        $ret = 0;
        $inc = 0;
        $dec = 0;
        $rep = 0;

        // Collect transactional records within date range
        $purchases = $product->purchaseItemsBetween($start, $end)->map(function ($item) use (&$pur){
            $pur += $item->quantity; // Track total purchases
            return [
                'date' => $item->purchase->date,
                'entry_id' => $item->purchase_id,
                'type' => 'Purchase',
                'credit' => $item->quantity,
                'debit' => 0,
            ];
        });

        $sales = $product->approvedItemsBetween($start, $end)->map(function ($item) use (&$sal) {
            $sal += $item->approvedquantity; // Track total sales
            return [
                'date' => $item->order->date,
                'entry_id' => $item->orderid,
                'type' => 'Sales',
                'credit' => 0,
                'debit' => $item->approvedquantity,
            ];
        });

        $returns = $product->salesReturnItemsBetween($start, $end)->map(function ($item) use (&$ret) {
            $ret += $item->quantity; // Track total returns
            return [
                'date' => $item->salesReturn->date,
                'entry_id' => $item->return_id,
                'type' => 'Sales Return',
                'debit' => 0,
                'credit' => $item->quantity,
            ];
        });

        $increased = $product->increasedAdjustmentItemsBetween($start, $end)->map(function ($item) use (&$inc) {
            $inc += $item->quantity; // Track total increases
            return [
                'date' => $item->purchase->date,
                'entry_id' => $item->purchase_adj_id,
                'type' => 'Adjustment (Increase)',
                'credit' => $item->quantity,
                'debit' => 0,
            ];
        });

        $decreased = $product->decreasedAdjustmentItemsBetween($start, $end)->map(function ($item) use (&$dec) {
            $dec += $item->quantity; // Track total decreases
            return [
                'date' => $item->purchase->date,
                'entry_id' => $item->purchase_adj_id,
                'type' => 'Adjustment (Decrease)',
                'credit' => 0,
                'debit' => $item->quantity,
            ];
        });

        $replaced = $product->damageReplacedWithOtherItemsBetween($start, $end)->map(function ($item) use (&$rep) {
            $rep += $item->quantity; // Track total replacements
            return [
                'date' => $item->damageItem->damage->date,
                'entry_id' => $item->invoice_id,
                'type' => 'Damage Replaced with Other',
                'credit' => 0,
                'debit' => $item->quantity, // ✅ correct: it's a stock decrease
            ];
        });

        // Combine all records and sort by date
        $entries = $entries
            ->merge($purchases)
            ->merge($sales)
            ->merge($returns)
            ->merge($increased)
            ->merge($decreased)
            ->merge($replaced)
            ->sortBy('date')
            ->values();

        return ['entries' => $entries, 'openingBalance' => $openingBalance,
            'totalPurchases' => $pur, 'totalSales' => $sal, 'totalReturns' => $ret, 'totalIncreased' => $inc,
            'totalDecreased' => $dec, 'totalReplaced' => $rep
        ];
    }


}
