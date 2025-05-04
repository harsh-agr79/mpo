<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductsPurchaseResource\Pages;
use App\Filament\Resources\ProductsPurchaseResource\RelationManagers;
use App\Models\Product;
use App\Models\ProductsPurchase;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductsPurchaseResource extends Resource
{
    protected static ?string $model = ProductsPurchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Transactions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->default(today())
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('purchase_id', getNepaliInvoiceId($state, true));
                    }),
                TextInput::make('purchase_id')
                    ->default(fn(Get $get) => getNepaliInvoiceId($get('date') ?? today()->format('Y-m-d'), true))
                    ->disabled()
                    ->dehydrated(),

                Repeater::make('items')
                    ->relationship()
                    ->reactive()
                    ->columns(2)
                    ->schema([
                        Select::make('prod_unique_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->options(Product::all()->pluck('name', 'prod_unique_id'))
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $product = Product::where('prod_unique_id', $state)->first();
                                if ($product) {
                                    $set('price', $product->price);

                                    // Immediately update total
                                    $quantity = (float) $get('quantity');
                                    $set('total', $quantity * $product->price);

                                    // ✅ Recalculate total_price
                                    $items = $get('../../items'); // navigate up the repeater context
                                    $grandTotal = collect($items)->sum('total');
                                    $set('../../total_price', $grandTotal);
                                }
                            }),

                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->reactive()
                            ->minValue(1)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $price = (float) ($get('price') ?? 0);
                                $quantity = (float) ($state ?? 1);
                                $set('total', $price * $quantity);

                                // ✅ Recalculate total_price
                                $items = $get('../../items'); // navigate up the repeater context
                                $grandTotal = collect($items)->sum('total');
                                $set('../../total_price', $grandTotal);
                            }),

                        TextInput::make('price')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->afterStateHydrated(function (callable $set, $get) {
                                if ($get('prod_unique_id')) {
                                    $product = Product::where('prod_unique_id', $get('prod_unique_id'))->first();
                                    if ($product) {
                                        // Set the price
                                        $set('price', $product->price);

                                        // Recalculate total if quantity is already set
                                        $quantity = (float) ($get('quantity') ?? 1);
                                        $set('total', $quantity * $product->price);


                                    }
                                }
                            }),

                        TextInput::make('total')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                    ])
                    ->afterStateUpdated(function (Set $set, $state, $get) {
                        // Recalculate total_price immediately when any item's state changes
                        $totalPrice = collect($get('items') ?? [])
                            ->sum(fn($item) => floatval($item['total'] ?? 0)); // Sum of all item totals
            
                        // Update grand total in the 'total_price' field
                        $set('total_price', $totalPrice);
                    })
                    ->createItemButtonLabel('Add Product')
                    ->required()
                    ->columnSpanFull()
                ,
                TextInput::make('total_price')
                    ->label('Total Price')
                    ->disabled()
                    ->dehydrated()
                    ->afterStateUpdated(function (callable $set, $state, $get) {
                        $set('total_price', collect($get('items'))->sum('total'));
                    })
                    ->afterStateHydrated(function (callable $set, $state, $get) {
                        // Recalculate grand total on form hydration
                        $set('total_price', collect($get('items'))->sum('total'));
                    })
                    ->reactive()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchase_id')->sortable()->searchable()->label('Purchase ID'),
                TextColumn::make('date')->label('Date (A.D.)')->date('Y-m-d'),
                TextColumn::make('nepali_date')->label('Date (B.S.)')
                    ->getStateUsing(fn($record) => getNepaliDate($record->date)),
                TextColumn::make('items_sum_quantity')
                    ->label('Total Quantity'),
                TextColumn::make('total_price')->label('Total Price')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductsPurchases::route('/'),
            'create' => Pages\CreateProductsPurchase::route('/create'),
            'edit' => Pages\EditProductsPurchase::route('/{record}/edit'),
        ];
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['total_price'] = collect($data['items'] ?? [])
            ->sum(fn($item) => $item['total'] ?? 0);

        return $data;
    }

    protected static function mutateFormDataBeforeSave(array $data): array
    {
        $data['total_price'] = collect($data['items'] ?? [])
            ->sum(fn($item) => $item['total'] ?? 0);

        return $data;
    }



}
