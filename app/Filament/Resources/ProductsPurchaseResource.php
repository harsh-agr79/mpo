<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogRelationManagerResource\RelationManagers\ActivityLogsRelationManager;
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
use ProductsPurchaseLogsRelationManager;
use DiscoveryDesign\FilamentGaze\Forms\Components\GazeBanner;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class ProductsPurchaseResource extends Resource
{
    protected static ?string $model = ProductsPurchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
       protected static ?string $navigationGroup = "Products";


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
                            $set('purchase_id', getNepaliInvoiceId($state, true));
                        }
                    }),
                TextInput::make('purchase_id')
                    ->default(
                        fn(Get $get, $context) =>
                        $context === 'create'
                        ? getNepaliInvoiceId($get('date') ?? today()->format('Y-m-d'), true)
                        : null
                    )
                    ->disabled()
                    ->dehydrated(),

              
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
                            ->dehydrated()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $price = (float) ($state ?? 0);
                                $quantity = (float) ($get('quantity') ?? 0);
                                $set('total', $price * $quantity);

                                // ✅ Recalculate total_price
                                $items = $get('../../items'); // navigate up the repeater context
                                $grandTotal = collect($items)->sum('total');
                                $set('../../total_price', $grandTotal);
                            }),

                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->reactive()
                            ->minValue(1)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $quantity = (float) ($state ?? 1);
                                $price = (float) ($get('price') ?? 0);
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
                            })
                ])
                ->reorderable()
                ->afterStateUpdated(function (Set $set, $state, $get) {
                        // Recalculate total_price immediately when any item's state changes
                        $totalPrice = collect($get('items') ?? [])
                            ->sum(fn($item) => floatval($item['total'] ?? 0)); // Sum of all item totals
            
                        // Update grand total in the 'total_price' field
                        $set('total_price', $totalPrice);
                    })
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
                TextColumn::make('nepali_date')->label('Date (B.S.)')
                    ->getStateUsing(fn($record) => getNepaliDate($record->date))->sortable()->toggleable(),
                TextColumn::make('purchase_id')->sortable()->searchable()->label('Purchase ID'),
                TextColumn::make('items_sum_quantity')
                    ->label('Total Quantity')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_price')->label('Total Price')
                    ->sortable()
                    ->toggleable()
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make()->size('xl')
                    ->label('')
                    ->modalHeading(fn($record) => 'Product Purchase: ' . ucfirst($record->purchase_id))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Section::make()
                            ->schema([

                                TextEntry::make('purchase_id')->label('PURCHASE ID'),
                                TextEntry::make('date')->label('DATE'),
                                TextEntry::make('total_price')->label('TOTAL PRICE')->money('npr'),
                                RepeatableEntry::make('items')
                                    ->label('Purchase Items')
                                    ->columnSpanFull()
                                    ->schema([
                                        TextEntry::make('product.name')->label('Product Name'),
                                        TextEntry::make('quantity')->label('Quantity'),
                                        TextEntry::make('price')->label('Price')->money('npr'),
                                    ])
                                    ->columns(3),
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
            ProductsPurchaseLogsRelationManager::class,
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
