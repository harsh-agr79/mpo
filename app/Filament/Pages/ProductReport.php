<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\User;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Filament\Forms\Form;
use Filament\Forms\Components\{Grid, Select};

class ProductReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Analytics';
    protected static string $view = 'filament.pages.product-report';

    // Public properties bound to the form
    public ?int $customerId = null;
    public ?int $categoryId = null;
    public ?int $startMonth = null;
    public ?int $endMonth = null;
    public ?int $startYear = null;
    public ?int $endYear = null;

    // For view-related display
    public $customers;
    public $categories;
    public $customer;
    public $category;

    public function getTitle(): string
    {
        return ''; // Disable default title rendering
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(4)->schema([
                Select::make('startMonth')
                    ->label('Start Month')
                    ->required()
                    ->options([1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7, 8=>8, 9=>9, 10=>10, 11=>11, 12=>12])
                    ->live(),

                Select::make('startYear')
                    ->label('Start Year')
                    ->required()
                    ->options([2077 => 2077, 2078 => 2078, 2079 => 2079, 2080 => 2080, 2081 => 2081, 2082 => 2082, 2083 => 2083, 2084 => 2084])
                    ->live(),

                Select::make('endMonth')
                    ->label('End Month')
                    ->required()
                    ->options([1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7, 8=>8, 9=>9, 10=>10, 11=>11, 12=>12])
                    ->live(),

                Select::make('endYear')
                    ->label('End Year')
                    ->required()
                    ->options([2077 => 2077, 2078 => 2078, 2079 => 2079, 2080 => 2080, 2081 => 2081, 2082 => 2082, 2083 => 2083, 2084 => 2084])
                    ->live(),

                Select::make('customerId')
                    ->label('Select Customer')
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->nullable()
                    ->live(),

                Select::make('categoryId')
                    ->label('Select Category')
                    ->options(Category::pluck('name', 'id'))
                    ->searchable()
                    ->nullable()
                    ->live(),
            ]),
        ]);
    }

    public function mount(?int $customerId = null): void
    {
        // Load dropdown options
        $this->customers = User::orderBy('name')->get();
        $this->categories = Category::orderBy('name')->get();

        // Set defaults if not already bound
        $this->startMonth ??= 1;
        $this->startYear ??= (int) getNepaliYear(now());
        $this->endMonth ??= (int) getNepaliMonth(now());
        $this->endYear ??= (int) getNepaliYear(now());

        $this->customerId = $customerId ?? request()->get('customerId');
        $this->categoryId = request()->get('categoryId');

        // Assign related models
        $this->customer = $this->customerId ? User::find($this->customerId) : null;
        $this->category = $this->categoryId ? Category::find($this->categoryId) : null;

        // Pre-fill form
        $this->form->fill([
            'startMonth' => $this->startMonth,
            'startYear' => $this->startYear,
            'endMonth' => $this->endMonth,
            'endYear' => $this->endYear,
            'customerId' => $this->customerId,
            'categoryId' => $this->categoryId,
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, [
            'startMonth', 'startYear',
            'endMonth', 'endYear',
            'customerId', 'categoryId'
        ])) {
            $this->customer = $this->customerId ? User::find($this->customerId) : null;
            $this->category = $this->categoryId ? Category::find($this->categoryId) : null;

            $this->dispatch('analyticsDataUpdated', $this->getDataProperty());
        }
    }

    public function getCustomersProperty(): array
    {
        return $this->customers->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
        ])->toArray();
    }

    public function getCategoriesProperty(): array
    {
        return $this->categories->map(fn($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
        ])->toArray();
    }
    
    public function getDataProperty()
    {
        // Step 1: Get list of months between start and end in BS
        $months = getNepaliMonthRange($this->startYear, $this->startMonth, $this->endYear, $this->endMonth);

        // Step 2: Build key mapping
        $monthKeys = [];
        foreach ($months as $entry) {
            $key = $entry['year'] . '-' . str_pad($entry['month'], 2, '0', STR_PAD_LEFT);
            $monthKeys[] = $key;
        }

        // Step 3: Get relevant category and product maps
        $categories = Category::with('products')->get();
        $categoryMap = $categories->mapWithKeys(fn($cat) => [$cat->id => $cat->name]);

        $productToCategory = [];
        $productMap = [];

        foreach ($categories as $category) {
            foreach ($category->products as $product) {
                $productToCategory[$product->id] = $category->id;
                $productMap[$product->id] = $product->name;
            }
        }

        // Step 4: Build table headers
        $columns = [];

        if ($this->categoryId) {
            // Products of selected category
            $selectedCategory = $categories->firstWhere('id', $this->categoryId);
            $productIds = $selectedCategory ? $selectedCategory->products->pluck('id')->toArray() : [];
            foreach ($productIds as $pid) {
                $columns[$pid] = $productMap[$pid] ?? 'Unknown';
            }
        } else {
            // Default: show all categories
            $columns = $categoryMap->toArray();
        }

        // Step 5: Get filtered orders
        $ordersQuery = Order::where('mainstatus', 'approved')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('nepyear', '>', $this->startYear)
                        ->orWhere(function ($q2) {
                            $q2->where('nepyear', $this->startYear)
                                ->where('nepmonth', '>=', $this->startMonth);
                        });
                })->where(function ($q) {
                    $q->where('nepyear', '<', $this->endYear)
                        ->orWhere(function ($q2) {
                            $q2->where('nepyear', $this->endYear)
                                ->where('nepmonth', '<=', $this->endMonth);
                        });
                });
            });

        if ($this->customerId) {
            $ordersQuery->where('user_id', $this->customerId);
        }

        $orders = $ordersQuery->pluck('orderid');

        // Step 6: Get approved items
        $items = OrderItem::with('order')
            ->whereIn('orderid', $orders)
            ->where('status', 'approved')
            ->get();

        // Step 7: Prepare empty table
        $data = [];
        foreach ($monthKeys as $key) {
            $data[$key] = [];

            foreach ($columns as $colKey => $colName) {
                $data[$key][$colName] = 0;
            }
        }

        // Step 8: Group items
        foreach ($items as $item) {
            $order = $item->order;
            if (!$order) continue;

            $year = $order->nepyear;
            $month = str_pad($order->nepmonth, 2, '0', STR_PAD_LEFT);
            $key = $year . '-' . $month;

            if (!isset($data[$key])) continue;

            if ($this->categoryId) {
                // Product-based grouping
                if (!isset($columns[$item->product_id])) continue;

                $productName = $columns[$item->product_id];
                $data[$key][$productName] += $item->approvedquantity;
            } else {
                // Category-based grouping
                $categoryId = $productToCategory[$item->product_id] ?? null;
                if (!$categoryId) continue;

                $categoryName = $columns[$categoryId] ?? 'Unknown';
                if (!isset($data[$key][$categoryName])) continue;

                $data[$key][$categoryName] += $item->approvedquantity;
            }
        }

        return [
            'categoryId' => $this->categoryId,
            'categories' => array_values($columns), // becomes product names if category is selected
            'data' => $data,
        ];
    }


}
