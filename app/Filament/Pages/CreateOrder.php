<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use App\Models\User;
use Filament\Actions\Action;

class CreateOrder extends Page {
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.create-order';

    public $quantities = [];
    public $selectedUser = null;
    public $order_date;
    public $search = '';

    public function getTitle(): string
    {
        return ''; // Ensure nothing is rendered
    }

    public function getActions(): array
    {
        return [
            Action::make('viewCart')
                ->label('View Cart')
                ->modalHeading('Your Cart')
                ->modalSubmitAction(false) // No submit button
                ->modalContent(function () {
                    return view('filament.pages.partials.cart', [
                        'cartItems' => $this->getCartItems(),
                        'total' => $this->getCartTotal(),
                    ]);
                }),
        ];
    }

    public function getCartItems()
    {
        return collect($this->quantities)
            ->filter(fn($qty) => $qty > 0)
            ->map(function ($qty, $id) {
                $product = \App\Models\Product::find($id);
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $qty,
                    'subtotal' => $product->price * $qty,
                ];
            })->values();
    }

    public function getCartTotal()
    {
        return $this->getCartItems()->sum('subtotal');
    }

    public function form( Form $form ): Form {
        return $form->schema( [
            Grid::make( 2 )->schema( [
                Select::make( 'selectedUser' )
                ->label( 'Select User' )
                ->options( User::pluck( 'name', 'id' ) )
                ->searchable()
                ->required(),
                DatePicker::make( 'order_date' )
                ->label( 'Order Date' )
                ->default( now() ) // ⬅️ sets today's date
                    ->required(),
            ])
            ]);
        
    }

    public function getCartTotalProperty()
    {
        return collect($this->quantities)
            ->filter(fn($qty) => $qty > 0)
            ->map(fn($qty, $id) => \App\Models\Product::find($id)->price * $qty)
            ->sum();
    }


    public function mount()
    {
        foreach (Product::all() as $product) {
            $this->quantities[$product->id] = "";
        }
        $this->form->fill([
            'selectedUser' => null,
            'order_date' => now()->toDateString(),
        ]);
    }

    public function checkout()
    {
        $cart = collect($this->quantities)
            ->filter(fn($qty) => $qty > 0)
            ->map(function ($qty, $productId) {
                $product = Product::find($productId);
                return [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $qty,
                    'price' => $product->price,
                    'subtotal' => $product->price * $qty,
                ];
            })->values()->toArray();

        \Log::info('Order:', $cart);

        $this->quantities = [];
        session()->flash('message', 'Order placed!');
    }

   public function getProductsProperty()
    {
        return Product::with('category')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->orderBy('categories.order_num') // Order by category order
            ->orderBy('products.order_num')   // Order within each category
            ->select('products.*') // Important: select only product fields to avoid column conflicts
            ->get();
    }

    public function getUserOptionsProperty()
    {
        return \App\Models\User::pluck('name', 'id' );
    }
}
