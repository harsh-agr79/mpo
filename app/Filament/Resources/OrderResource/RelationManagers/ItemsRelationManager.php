<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Components\{TextInput, Select, Hidden};
use Filament\Tables\Columns\{TextColumn, BadgeColumn};
use App\Models\Product;
use Filament\Actions\StaticAction;
use Filament\Tables\Actions\Action;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $this->updatePrices($state, $set)),

                TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->reactive(),

                TextInput::make('price')
                    ->label('Price')
                    ->prefix('रु')
                    ->numeric()
                    ->readonly(), // Prevent manual input
                TextInput::make('offer')
                    ->label('offer')
                    ->readonly(), // Prevent manual input

                Hidden::make('status')
                    ->default('pending'),
                Hidden::make('approvedquantity')
                    ->default(0),

                
                Hidden::make('orderid')
                    ->default(fn ($livewire) => $livewire->ownerRecord->orderid),
                // Forms\Components\TextInput::make('orderid')
                //     ->required()
                //     ->maxLength(255),
            ]);
    }

    public function updatePrices($productId, $set)
        {
            $product = Product::find($productId);
            if ($product) {
                $set('price', $product->price);
                $set('offer', $product->offer); // Assuming 'offer' is a DB field
            } else {
                $set('price', null);
                $set('offer', null);
            }
        }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('orderid')
            ->poll('3s')
            ->columns([
                Tables\Columns\ImageColumn::make('product.image')
                    ->label('Image')
                    ->square()
                    ->width(100)
                    ->height(100)
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('Product.name')
                    ->label('Product Name')
                    ->sortable()
                    ->wrap(),
                Tables\Columns\SelectColumn::make('offer')
                   
                 ->options(function ($record) {
                        // If you have a relationship called `product`, you can use:
                        $product = $record->product ?? Product::find($record->product_id);

                        if (! $product || ! $product->offer) {
                            return [];
                        }

                        $offers = $product->offer;

                        return collect($offers)
                            ->mapWithKeys(fn ($price, $qty) => [json_encode([$qty => $price]) => "{$qty} pcs : ₹{$price}"])
                            ->toArray();
                    })
                    ->searchable()
                    ->sortable()
                    ->label('Offer (pcs:price)'),
                Tables\Columns\TextColumn::make('quantity')->color('success'),
                Tables\Columns\TextInputColumn::make('approvedquantity')
                    ->label('Approved Quantity')->rules(['integer']),
                Tables\Columns\TextInputColumn::make('price')->rules(['integer']),
                Tables\Columns\SelectColumn::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->afterStateUpdated(function ($record, $state) {
                         $quantity = (int) $record->quantity;

                        if ($state === 'approved') {
                            if ($record->approvedquantity <= 0) {
                                $record->approvedquantity = $quantity;
                            }
                            // else: keep approvedquantity as is
                        } else {
                            // If changed to pending or rejected
                            $record->approvedquantity = 0;
                        }

                        // $record->status = $state;
                        $record->save();
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Total')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->status === 'pending' || $record->status === 'rejected') {
                            return '0';
                        }
                        return $record->price * $record->approvedquantity;
                    }),
            ])
            ->filters([
                //
            ])
            ->paginated(false)
            ->headerActions([
                  Action::make('total_head')
                    ->label(fn ($livewire) => 'Total: ' . number_format(
                        $livewire->ownerRecord->total
                    ))
                    ->disabled()
                    ->color('danger'),
                      Action::make('net_total_head')
                    ->label(fn ($livewire) => 'Net Total: ' . number_format(
                        $livewire->ownerRecord->net_total
                    ))
                    ->disabled()
                    ->color('success'),
               
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('bulkApproveAll')
                        ->label('Approve All Items')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function () {
                            $items = $this->getOwnerRecord()->items;

                            foreach ($items as $record) {
                                $quantity = (int) $record->quantity;

                                if ($record->approvedquantity <= 0) {
                                    $record->approvedquantity = $quantity;
                                }

                                $record->status = 'approved';
                                $record->save();
                            }
                        }),

                    Tables\Actions\Action::make('bulkRejectAll')
                        ->label('Reject All Items')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function () {
                            $items = $this->getOwnerRecord()->items;

                            foreach ($items as $record) {
                                $record->status = 'rejected';
                                $record->approvedquantity = 0;
                                $record->save();
                            }
                        }),

                    Tables\Actions\Action::make('bulkPendingAll')
                            ->label('Mark All as Pending')
                            ->icon('heroicon-o-clock')
                            ->color('warning')
                            ->action(function () {
                                $items = $this->getOwnerRecord()->items;

                                foreach ($items as $record) {
                                    $record->status = 'pending';
                                    $record->approvedquantity = 0;
                                    $record->save();
                                }
                            }),
                ])
                ->label('Status Update')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('info')
                ->button(),
                 Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->icon('')->label(''),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
