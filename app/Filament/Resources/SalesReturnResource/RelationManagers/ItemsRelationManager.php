<?php

namespace App\Filament\Resources\SalesReturnResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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

                    $alreadyAddedProductIds = collect($parentRecord?->items ?? [])
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
                    ->reactive(),

                TextInput::make('price')
                    ->label('Price')
                    ->prefix('₹')
                    ->numeric()
                    ->readonly(),

                 Hidden::make('return_id')->default(fn ($livewire) => $livewire->ownerRecord->return_id),
            ]);
    }

    public function updateOfferAndPrice($productId, $quantity, callable $set, $livewire)
    {
        $product = Product::find($productId);
        $isEditing = !is_null($livewire->mountedTableActionRecord);

       
        $set('price', $product->price);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('return_id')
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
                    ->size(TextColumn\TextColumnSize::ExtraSmall),
                 Tables\Columns\TextInputColumn::make('quantity')
                    ->label('Quantity')->rules(['integer']),
                Tables\Columns\TextInputColumn::make('price')->rules(['integer']),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Total')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->formatStateUsing(function ($state, $record) {
                        return $record->price * $record->quantity;
                    })
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
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
