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
                ->searchable()
                ->required()
                ->reactive()
                ->options(function (callable $get, $livewire) {
                    $parentRecord = $livewire->ownerRecord ?? null;

                    $alreadyAddedProductIds = collect($parentRecord?->orderitems ?? [])
                        ->pluck('product_id')
                        ->filter() // Remove nulls
                        ->toArray();

                    return \App\Models\Product::query()
                        ->when(!empty($alreadyAddedProductIds), fn ($query) =>
                            $query->whereNotIn('id', $alreadyAddedProductIds)
                        )
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->afterStateUpdated(fn ($state, callable $set, $get, $livewire) =>
                    $this->updateOfferAndPrice($state, $get('quantity'), $set, $livewire)
                ),

                TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set, $get, $livewire) =>
                        $this->updateOfferAndPrice($get('product_id'), $state, $set, $livewire)
                    ),

                TextInput::make('price')
                    ->label('Price')
                    ->prefix('₹')
                    ->numeric()
                    ->readonly(),

                // ✅ Offer stored in JSON format
                TextInput::make('offer')
                    ->label('Offer')
                    ->helperText('Auto-filled as JSON, e.g. {"10": "1000"}')
                    ->reactive()
                    ->readonly(),

                Hidden::make('status')->default('pending'),
                Hidden::make('actualprice'),
                Hidden::make('approvedquantity')->default(0),
                Hidden::make('orderid')->default(fn ($livewire) => $livewire->ownerRecord->orderid),
        ]);
    }


    public function updateOfferAndPrice($productId, $quantity, callable $set, $livewire)
    {
        $product = Product::find($productId);
        $isEditing = !is_null($livewire->mountedTableActionRecord);

        if (!$product || !$quantity) {
            $set('offer', null);
            return;
        }

        $offers = $product->offer ?? []; // JSON decoded offer: {"5":"1200", "10":"1000"}
        $bestMatch = null;
        $bestQty = null;

        // Find best match (highest offer qty ≤ input quantity)
        foreach ($offers as $offerQty => $offerPrice) {
            if ((int)$offerQty <= $quantity) {
                if (is_null($bestQty) || (int)$offerQty > $bestQty) {
                    $bestQty = (int)$offerQty;
                    $bestMatch = $offerPrice;
                }
            }
        }

        // Update offer field only on creation (not editing)
        if (!$isEditing && $bestMatch !== null) {
            $set('offer', json_encode([$bestQty => $bestMatch]));
        }

        // Set price field logic
        if ($isEditing) {
            if ($bestMatch && $livewire->mountedTableActionRecord->status === 'approved') {
                $set('price', $bestMatch);
            } else {
                $set('price', $product->price);
            }
        } else {
            $set('price', $product->price);
        }
        if(!$isEditing){
            $set('actualprice', $product->price);
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
                    ->width(50)
                    ->height(50)
                    ->sortable()
                    ->toggleable(),
              Tables\Columns\TextColumn::make('Product.name')
                ->label('Product Name')
                ->sortable()
                ->wrap()
                ->size(TextColumn\TextColumnSize::ExtraSmall)
                ->formatStateUsing(function ($state, $record) {
                    return $record->product->stock == 1
                        ? '<span style="border-bottom: 3px solid red; padding-bottom: 1px;">' . e($state) . '</span>'
                        : e($state);
                })
                ->html(),
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
                Tables\Columns\TextColumn::make('quantity')
                    ->size(TextColumn\TextColumnSize::ExtraSmall),
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
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
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
             ->recordClasses(function ($record) {

                if ($record->status === 'pending') return 'bg-status-pending';
                if ($record->status === 'rejected') return 'bg-status-rejected';
                if ($record->status === 'approved' && ($record->offer == "[]" || $record->offer == null)) return 'bg-status-approved';
                if ($record->status === 'approved') return 'bg-status-packing';
                // if ($record->clnstatus === 'delivered' && $record->delivered_at !== null) return 'bg-status-delivered';

                return '';
            })
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
                 Tables\Actions\CreateAction::make()->label('Add Item')->icon('heroicon-o-plus-circle'),
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
