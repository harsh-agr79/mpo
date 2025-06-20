<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogRelationManagerResource\RelationManagers\ActivityLogsRelationManager;
use App\Filament\Resources\ProductsPurchaseAdjustmentResource\Pages;
use App\Filament\Resources\ProductsPurchaseAdjustmentResource\RelationManagers;
use App\Models\Product;
use App\Models\ProductsPurchaseAdjustment;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use ProductsPurchaseAdjustmentLogsRelationManager;
use DiscoveryDesign\FilamentGaze\Forms\Components\GazeBanner;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class ProductsPurchaseAdjustmentResource extends Resource
{
    protected static ?string $model = ProductsPurchaseAdjustment::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
        protected static ?string $navigationGroup = "Products";


    protected static ?string $navigationLabel = 'Products Adjustment';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                  GazeBanner::make()
                ->pollTimer(5)
                ->lock()
                ->canTakeControl(fn() => auth()->user()?->hasRole('Admin'))
                ->hideOnCreate()
                ->columnSpanFull(),
                DatePicker::make('date')
                    ->default(today())
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, $state, $context) {
                        if ($context == 'create') {
                            $set('purchase_adj_id', getAdjustmentInvoiceId($state));
                        }
                    }),
                TextInput::make('purchase_adj_id')
                    ->default(
                        fn(Get $get, $context) =>
                        $context === 'create'
                        ? getAdjustmentInvoiceId($get('date') ?? today()->format('Y-m-d'))
                        : null
                    )
                    ->disabled()
                    ->dehydrated(),

                // Repeater::make('items')
                //     ->relationship()
                //     ->reactive()
                //     ->columns(2)
                //     ->schema([
                //         Select::make('prod_unique_id')
                //             ->label('Product')
                //             ->relationship('product', 'name')
                //             ->required()
                //             ->reactive()
                //             ->searchable()
                //             ->options(Product::all()->pluck('name', 'prod_unique_id')),

                //         TextInput::make('price')
                //             ->numeric()
                //             ->reactive()
                //             ->dehydrated()
                //             ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                 $price = (float) ($state ?? 0);
                //                 $quantity = (float) ($get('quantity') ?? 0);
                //                 $set('total', $price * $quantity);

                //                 // ✅ Recalculate total_price
                //                 $items = $get('../../items'); // navigate up the repeater context
                //                 $grandTotal = collect($items)->sum('total');
                //                 $set('../../total_price', $grandTotal);
                //             })
                //         ,
                //         TextInput::make('quantity')
                //             ->numeric()
                //             ->required()
                //             ->default(1)
                //             ->reactive()
                //             ->minValue(1)
                //             ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //                 $price = (float) ($get('price') ?? 0);
                //                 $quantity = (float) ($state ?? 1);
                //                 $set('total', $price * $quantity);

                //                 // ✅ Recalculate total_price
                //                 $items = $get('../../items'); // navigate up the repeater context
                //                 $grandTotal = collect($items)->sum('total');
                //                 $set('../../total_price', $grandTotal);
                //             }),


                //         TextInput::make('total')
                //             ->numeric()
                //             ->disabled()
                //             ->dehydrated()
                //             ->afterStateHydrated(function ($state, callable $set, callable $get) {
                //                 $price = (float) ($get('price') ?? 0);
                //                 $quantity = (float) ($get('quantity') ?? 0);

                //                 $set('total', $price * $quantity);
                //             }),

                //         Select::make('type')
                //             ->searchable()
                //             ->options([
                //                 'increase' => 'Increase',
                //                 'decrease' => 'Decrease'
                //             ])
                //             ->required()
                //     ])
                //     ->afterStateUpdated(function (Set $set, $state, $get) {
                //         // Recalculate total_price immediately when any item's state changes
                //         $totalPrice = collect($get('items') ?? [])
                //             ->sum(fn($item) => floatval($item['total'] ?? 0)); // Sum of all item totals
            
                //         // Update grand total in the 'total_price' field
                //         $set('total_price', $totalPrice);
                //     })
                //     ->createItemButtonLabel('Add Product')
                //     ->required()
                //     ->columnSpanFull()
                // ,

                 TableRepeater::make('items')
                ->relationship('items')
                ->schema([
                   Select::make('prod_unique_id')
                            ->label('Product')
                            ->relationship('product', 'name')
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->options(Product::all()->pluck('name', 'prod_unique_id')),

                        TextInput::make('price')
                            ->numeric()
                            ->reactive()
                            ->dehydrated()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $price = (float) ($state ?? 0);
                                $quantity = (float) ($get('quantity') ?? 0);
                                $set('total', $price * $quantity);

                                // ✅ Recalculate total_price
                                $items = $get('../../items'); // navigate up the repeater context
                                $grandTotal = collect($items)->sum('total');
                                $set('../../total_price', $grandTotal);
                            })
                        ,
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


                        TextInput::make('total')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->afterStateHydrated(function ($state, callable $set, callable $get) {
                                $price = (float) ($get('price') ?? 0);
                                $quantity = (float) ($get('quantity') ?? 0);

                                $set('total', $price * $quantity);
                            }),

                        Select::make('type')
                            ->searchable()
                            ->options([
                                'increase' => 'Increase',
                                'decrease' => 'Decrease'
                            ])
                            ->required()
                ])
                ->reorderable()
                ->afterStateUpdated(function (Set $set, $state, $get) {
                        // Recalculate total_price immediately when any item's state changes
                        $totalPrice = collect($get('items') ?? [])
                            ->sum(fn($item) => floatval($item['total'] ?? 0)); // Sum of all item totals
            
                        // Update grand total in the 'total_price' field
                        $set('total_price', $totalPrice);
                })
                ->createItemButtonLabel('Add Product')
                ->columnSpan('full'),
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
            ->poll('10s')
            ->columns([
                TextColumn::make('date')->sortable()->label('Date (A.D.)')->date('Y-m-d'),
                TextColumn::make('nepali_date')->sortable()->label('Date (B.S.)')
                    ->getStateUsing(fn($record) => getNepaliDate($record->date))
                    ->toggleable(),
                TextColumn::make('purchase_adj_id')->sortable()->searchable()->label('Purchase Adjustment ID'),
                TextColumn::make('items_sum_quantity')
                    ->label('Total Quantity')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_price')->label('Total Price')->sortable()->toggleable()
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make()->size('xl')
                    ->label('')
                    ->modalHeading(fn($record) => 'Product Adjustment: ' . ucfirst($record->purchase_adj_id))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Section::make()
                            ->schema([

                                TextEntry::make('purchase_adj_id')->label('ADJUSTMENT ID'),
                                TextEntry::make('date')->label('DATE'),
                                TextEntry::make('total_price')->label('TOTAL PRICE')->money('npr'),
                                RepeatableEntry::make('items')
                                    ->label('Adjusted Items')
                                    ->columnSpanFull()
                                    ->schema([
                                        TextEntry::make('product.name')->label('Product Name'),
                                        TextEntry::make('quantity')->label('Quantity'),
                                        TextEntry::make('type')->label('Type'),
                                        TextEntry::make('price')->label('Price')->money('npr'),
                                    ])
                                    ->columns(4),
                                TextEntry::make('created_at')->label('CREATED_AT'),
                                TextEntry::make('updated_at')->label('UPDATED_AT'),
                                TextEntry::make('deleted_at')->label('DELETED_AT')->visible(fn($record) => filled($record->deleted_at)),

                            ])
                            ->columns(2),
                    ]),
                Tables\Actions\EditAction::make()->size('xl')->label(''),
                Tables\Actions\DeleteAction::make()->size('xl')->label(''),
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
            ProductsPurchaseAdjustmentLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductsPurchaseAdjustments::route('/'),
            'create' => Pages\CreateProductsPurchaseAdjustment::route('/create'),
            'edit' => Pages\EditProductsPurchaseAdjustment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
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
