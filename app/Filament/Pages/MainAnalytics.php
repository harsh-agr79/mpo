<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;
use Filament\Forms\Form;
use Filament\Forms\Components\ {
    Grid, Select, DatePicker}
    ;

    class MainAnalytics extends Page {
        protected static ?string $navigationIcon = 'heroicon-o-document-text';

        protected static string $view = 'filament.pages.main-analytics';

        public ?int $customerId = null;
        public ?string $startDate = null;
        public ?string $endDate = null;
        public $selectedCustomerId;
        public $customers;
        public $customer;

        public function getTitle(): string {
            return '';
            // Ensure nothing is rendered
        }

        public function form( Form $form ): Form {
            return $form->schema( [
                Grid::make( 3 )->schema( [
                    Select::make( 'customerId' )
                    ->label( 'Select Customer' )
                    ->options( User::pluck( 'name', 'id' ) )
                    ->searchable()
                    ->nullable()
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
            if ( in_array( $property, [ 'customerId', 'startDate', 'endDate' ] ) ) {
                $this->customer = $this->customerId ? User::find( $this->customerId ) : null;
                $this->getDataProperty();
                // Optional: wrap in a check if needed
                $this->dispatch('analyticsDataUpdated', $this->getDataProperty());
            }
        }

        public function mount( ?int $customerId = null ): void {
            $this->customers = User::orderBy( 'name' )->get();
            $this->selectedCustomerId = $customerId ?? request()->get( 'customerId' );

            $this->customerId = $this->selectedCustomerId;
            $this->customer = $this->customerId ? User::find( $this->customerId ) : null;

            $this->startDate = request()->get( 'startDate' ) ?? getStartOfFiscalYear();
            $this->endDate = request()->get( 'endDate' ) ?? getEndOfFiscalYear();

            $this->form->fill( [
                'customerId' => $this->customerId,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
            ] );
        }

        public function getCustomersProperty(): array {
            return $this->customers->map( function ( $customer ) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                ];
            }
        )->toArray();
    }

    public function getDataProperty() {
        $orderQuery = Order::query()
        ->where( 'mainstatus', 'approved' )
        ->whereBetween( 'date', [ $this->startDate, $this->endDate ] );

        if ( $this->customerId ) {
            $orderQuery->where( 'user_id', $this->customerId );
        }

        $orderIds = $orderQuery->pluck( 'orderid' );

        $orderItems = OrderItem::whereIn( 'orderid', $orderIds )
        ->where( 'status', 'approved' )
        ->get();

        $categories = \App\Models\Category::with( 'products' )->get();

        $totalOverallSales = 0;
        $categoryStats = [];

        foreach ( $categories as $category ) {
            $categoryTotalQty = 0;
            $categoryTotalSales = 0;

            $productStats = [];

            foreach ( $category->products as $product ) {
                $items = $orderItems->where( 'product_id', $product->id );

                $productQty = $items->sum( 'approvedquantity' );
                $productSales = $items->sum( fn ( $item ) => $item->approvedquantity * $item->price );

                $categoryTotalQty += $productQty;
                $categoryTotalSales += $productSales;

                $productStats[] = [
                    'product_name' => $product->name,
                    'product_id' => $product->id,
                    'quantity' => $productQty,
                    'sales' => $productSales,
                ];
            }

            // 🔽 Sort products inside category by sales DESC
            $sortedProducts = collect( $productStats )->sortByDesc( 'sales' )->values()->all();

            $totalOverallSales += $categoryTotalSales;

            $categoryStats[] = [
                'category_name' => $category->name,
                'category_id' => $category->id,
                'total_quantity' => $categoryTotalQty,
                'total_sales' => $categoryTotalSales,
                'products' => $sortedProducts,
            ];
        }

        // 🔽 Sort categories by total sales DESC
        $sortedCategoryStats = collect( $categoryStats )->sortByDesc( 'total_sales' )->values()->all();

        return [
            'overall_sales' => $totalOverallSales,
            'categories' => $sortedCategoryStats,
        ];
    }

}
