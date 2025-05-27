<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Material;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use App\Models\User;
use Filament\Actions\Action;
use App\Models\MaterialInvoice;
use App\Models\MaterialInvoiceItem;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class createMaterialInvoice extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.create-material-invoice';

     protected static ?string $navigationGroup = 'Materials';

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
                    return view('filament.pages.partials.materialCart', [
                        'cartItems' => $this->getCartItems(),
                        // 'total' => $this->getCartTotal()
                    ]);
                })->extraModalFooterActions([
                Action::make('Checkout')
                ->label('Checkout')
                ->color('success')
                ->icon('heroicon-m-shopping-cart')
                ->requiresConfirmation()
                ->action(fn () => $this->checkout()),
            ])
        ];
    }

     public function getCartItems()
    {
        return collect($this->quantities)
            ->filter(fn($qty) => $qty > 0)
            ->map(function ($qty, $id) {
                $material = \App\Models\Material::find($id);
                return [
                    'id' => $material->id,
                    'name' => $material->name,
                    // 'price' => $product->price,
                    'quantity' => $qty,
                    // 'subtotal' => $product->price * $qty,
                ];
            })->values();
    }

    public function checkout()
    {   
        $invoiceid = time().$this->selectedUser;
        $invoice = MaterialInvoice::create([
            'user_id' => $this->selectedUser,
            'invoice_id' => $invoiceid,
            'mainstatus' => 'pending',
            'date' => $this->order_date,
            'nepmonth' => getNepaliMonth($this->order_date),
            'nepyear' => getNepaliYear($this->order_date),
        ]);
            foreach ($this->getCartItems() as $item) {
                $material = \App\Models\Material::find($item['id']);
                $quantity = $item['quantity'];

                MaterialInvoiceItem::create([
                    'invoice_id' => $invoice->invoice_id,
                    'material_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'status' => 'pending'
                ]);
            }
        $this->selectedUser = null;
        $this->order_date = now()->toDateString();
        foreach ($this->quantities as $key => $val) {
            $this->quantities[$key] = '';
        }
        // $this->dispatch('close-modal');
        Notification::make()
            ->title('Material Invoice Created!')
            ->success()
            ->send();
        return redirect('/admin/material-invoices');
        
    }

     public function form( Form $form ): Form {
        return $form->schema( [
            Grid::make( 2 )->schema( [
                Select::make( 'selectedUser' )
                ->label( 'Select User' )
                ->options( User::pluck( 'name', 'id' ) )
                ->searchable()
                ->required()
                 ->createOptionForm([
                    TextInput::make('userid')
                        ->required(),
                ])
                ->createOptionUsing(function (array $data) {
                    $user = User::create([
                        'name' => $data['userid'], 
                        'userid' => $data['userid'],
                        'email' => $data['userid'].'@mypowerworld.com',
                        'password' =>  Hash::make(Str::random(12)),
                        'contact' => random_int(1000000000, 9999999999),
                        'type' => 'retailer',    
                    ]);

                    return $user->id;
                }),
                DatePicker::make( 'order_date' )
                ->label( 'Order Date' )
                ->default( now() ) // ⬅️ sets today's date
                    ->required(),
            ])
            ]);
        
    }

    public function mount()
    {
        foreach (Material::all() as $material) {
            $this->quantities[$material->id] = "";
        }
        $this->form->fill([
            'selectedUser' => null,
            'order_date' => now()->toDateString(),
        ]);
    }

    public function getMaterialsProperty()
    {
        return Material::all();
    }

    public function getUserOptionsProperty()
    {
        return \App\Models\User::pluck('name', 'id' );
    }
}
