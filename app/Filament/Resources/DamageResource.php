<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DamageResource\Pages;
use App\Filament\Resources\DamageResource\RelationManagers;
use App\Models\Damage;
use App\Models\Batch;
use App\Models\Problem;
use App\Models\Part;
use App\Models\Product;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DateTimePicker;
use Closure;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\{ColorColumn, CheckboxColumn, ToggleColumn, TextColumn, BooleanColumn, DateTimeColumn};
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Validation\ValidationException;
// use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Filament\Forms\Get;
use Filament\Forms\Set;

class DamageResource extends Resource
{
    protected static ?string $model = Damage::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';
    protected static ?string $navigationGroup = 'Damage Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DateTimePicker::make('date')->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->options(User::all()->pluck('name', 'id'))
                    ->required(),
                TextInput::make('invoice_id')->reactive(),
                Grid::make(4)->schema([
                    Toggle::make('set_sendbycus_now')
                        ->label('Send by Customer')
                        
                         ->live() 
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $set('sendbycus',  now()->format('Y-m-d\TH:i:s'));
                            }
                            else {
                                $set('sendbycus', null);
                            }
                        }),

                    DateTimePicker::make('sendbycus')
                        ->label('Send by Customer')
                         ->live() 
                        ->nullable(),

                    Toggle::make('set_recbycomp_now')
                        ->label('Received by Company')
                        
                         ->live() 
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $set('recbycomp',  now()->format('Y-m-d\TH:i:s'));
                            }
                             else {
                                $set('recbycomp', null);
                            }
                        }),

                    DateTimePicker::make('recbycomp')
                        ->label('Received by Company')
                         ->live() 
                        ->nullable(),

                    Toggle::make('set_sendbackbycomp_now')
                        ->label('Send Back by Company')
                        
                         ->live() 
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $set('sendbackbycomp',  now()->format('Y-m-d\TH:i:s'));
                            }
                             else {
                                $set('sendbackbycomp', null);
                            }
                        }),

                    DateTimePicker::make('sendbackbycomp')
                        ->label('Send Back by Company')
                         ->live() 
                        ->nullable(),

                    Toggle::make('set_recbycus_now')
                        ->label('Received by Customer')
                         ->live() 
                        
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $set('recbycus',  now()->format('Y-m-d\TH:i:s'));
                            }
                             else {
                                $set('recbycus', null);
                            }
                        }),

                    DateTimePicker::make('recbycus')
                        ->label('Received by Customer')
                         ->live() 
                        ->nullable(),
                    ]),
                Textarea::make('remarks')->columnSpanFull(),
                Repeater::make('damageItems') ->relationship('damageItems')->columnSpanFull()
                        ->schema([
                            Grid::make(4)->schema([
                                Select::make('product_id')
                            ->relationship('product', 'name')
                            ->options(Product::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                             ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Loop through all nested damageItemDetails and update
                                $details = $get('damageItemDetails') ?? [];

                                foreach ($details as $index => $detail) {
                                    $set("damageItemDetails.{$index}.product_id", $state);

                                    // Reset dependent fields
                                    $set("damageItemDetails.{$index}.batch_id", null);
                                    $set("damageItemDetails.{$index}.problem_id", null);
                                    $set("damageItemDetails.{$index}.replaced_part", null);
                                    // $set("damageItemDetails.{$index}.replaced_product", null);
                                    // $set("damageItemDetails.{$index}.condition", null);
                                    // $set("damageItemDetails.{$index}.warranty", null);
                                    // $set("damageItemDetails.{$index}.warrantyproof", null);
                                }
                            }),
                            TextInput::make('quantity')->numeric()->minValue(1)->required()->reactive(),
                            TextInput::make('cusremarks')->label('Customer Remarks'),
                            Select::make('instatus')
                                ->options([
                                    'pending' => 'Pending',
                                    // 'In Progress' => 'In Progress',
                                    'completed' => 'Completed',
                                ])
                                ->default('pending')
                                ->required(),
                            ]),
                           
                            Repeater::make('damageItemDetails')
                            ->relationship('damageItemDetails')
                             ->reactive()
                               ->disableItemCreation(function (Get $get) {
                                    $items = collect($get('damageItemDetails') ?? []);

                                    $sum = $items->sum(function ($item) {
                                        return is_numeric($item['quantity'] ?? null) ? (int) $item['quantity'] : 0;
                                    });

                                    $parentQty = is_numeric($get('quantity')) ? (int) $get('quantity') : 0;

                                    return $sum >= $parentQty;
                                })
                                ->mutateRelationshipDataBeforeSaveUsing(function (array $data, Get $get): array {
                                       $items = collect($get('damageItemDetails') ?? []);
                                        $sum = $items->sum(function ($item) {
                                            return is_numeric($item['quantity'] ?? null) ? (int) $item['quantity'] : 0;
                                        });
                                        $parentQty = is_numeric($get('quantity')) ? (int) $get('quantity') : 0;
                                         $productId = $get('product_id');
                                         $productName = Product::find($productId)?->name ?? 'Unknown Product';
                                        
                                        if ($sum > $parentQty) {
                                            Notification::make()
                                            ->title('Cannot Save: Quantity Exceeded')
                                            ->body("$productName: Details total ($sum) > Parent Qty ($parentQty)")
                                            ->danger()
                                            // ->persistent()
                                            ->send();
                                            
                                             throw ValidationException::withMessages([
                                                'damageItems' => "$productName: Reduce quantities by " . ($sum - $parentQty),
                                            ]);
                                            // Return original data to prevent changes
                                            return $get('damageItemDetails') ?? [];
                                        }
                                        
                                        return $data;
                                    })
                                ->helperText(function (Get $get) {
                                        $items = collect($get('damageItemDetails') ?? []);
                                        $sum = $items->sum(function ($item) {
                                            return is_numeric($item['quantity'] ?? null) ? (int) $item['quantity'] : 0;
                                        });
                                        $parentQty = is_numeric($get('quantity')) ? (int) $get('quantity') : 0;
                                        
                                        if ($sum >= $parentQty) {
                                            return "Maximum items reached (total quantity: {$sum}/{$parentQty})";
                                        }
                                        
                                        return null;
                                    })
                                ->schema([
                                    Hidden::make('invoice_id')
                                    ->default(fn (Get $get) => $get('../../../../invoice_id')) // goes up 4 levels to root
                                    ->afterStateHydrated(fn (Set $set, Get $get) => $set('invoice_id', $get('../../../../invoice_id')))
                                    ->dehydrated()
                                    ->reactive(),
                                    Grid::make(4)->schema([
                                   Hidden::make('product_id')
                                    ->default(fn (callable $get) => $get('../../product_id'))
                                    ->afterStateHydrated(fn (callable $set, callable $get) => $set('product_id', $get('../../product_id') ?? 0))
                                    ->dehydrated()
                                    ->required(),
                                   TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                     ->minValue(1)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state, $context) {
                                            $state = is_numeric($state) ? (int) $state : 0;
                                            $parentQty = is_numeric($get('quantity')) ? (int) $get('quantity') : 0;
                                            $details = $get('damageItemDetails') ?? [];

                                            $sum = 0;
                                            foreach ($details as $i => $detail) {
                                                $sum += is_numeric($detail['quantity'] ?? null) ? (int) $detail['quantity'] : 0;
                                            }

                                            if ($sum > $parentQty) {
                                                $excess = $sum - $parentQty;
                                                $correctedQty = max(0, $state - $excess);

                                                $details[$context['index']]['quantity'] = $correctedQty;
                                                $set('damageItemDetails', $details);
                                            }
                                    }),
                                   Select::make('condition')
                                    ->options([
                                        'new' => 'New',
                                        'old' => 'Old',
                                    ])
                                    ->reactive()
                                    ->required()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state !== 'old') {
                                            $set('warranty', null);
                                            $set('warrantyproof', null); // Also clear deeper fields
                                        }
                                    }),

                               Select::make('warranty')
                                ->label('Warranty')
                                ->options([
                                    'Under warranty' => 'Under warranty',
                                    'warranty Expired' => 'warranty Expired',
                                    'Item not under warranty' => 'Item not under warranty',
                                    'Warranty Info missing(RCP)' => 'Warranty Info missing(RCP)',
                                ])
                                ->placeholder('Select warranty')
                                ->reactive()
                                ->visible(fn (callable $get) => $get('condition') === 'old')
                                ->dehydrated(fn (callable $get) => $get('condition') === 'old')
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state !== 'Under warranty') {
                                        $set('warrantyproof', null);
                                    }
                                }),

                            Select::make('warrantyproof')
                                ->label('Warranty Proof')
                                ->options([
                                    'warranty card' => 'warranty card',
                                    'purchase bill' => 'purchase bill',
                                    'Marked with Marker' => 'Marked with Marker',
                                    'Online purchase proof' => 'Online purchase proof',
                                ])
                                ->placeholder('Select warranty proof')
                                ->reactive()
                                ->visible(fn(callable $get) => $get('warranty') === 'Under warranty')
                                ->dehydrated(fn (callable $get) => $get('warranty') === 'Under warranty'),

                               

                                   Select::make('batch_id')
                                    ->label('Batch')
                                    ->searchable()
                                    ->options(function (callable $get) {
                                        $productId = $get('../../product_id'); // go two levels up to access parent product_id
                                        if (!$productId) return [];
                                        return \App\Models\Batch::where('product_id', $productId)
                                            ->pluck('batch_no', 'id');
                                    })
                                    ->required()
                                    ->reactive(),
                                    // ->afterStateUpdated(fn ($set) => $set('batch_id', null)),
                                    Select::make('problem_id')
                                    ->label('Problem')
                                    ->options(function (callable $get) {
                                        $productId = $get('../../product_id');

                                        if (!$productId) return [];

                                        $product = \App\Models\Product::with('category')->find($productId);

                                        if (!$product || !$product->category) return [];

                                        return \App\Models\Problem::all()
                                            ->filter(fn($problem) => in_array($product->category->id, $problem->category_id ?? []))
                                            ->pluck('problem', 'id');
                                    })
                                    ->searchable()
                                    ->required(),
                                    Select::make('solution')
                                        ->label('Solution')
                                        ->options([
                                            'repaired(same product)' => 'repaired(same product)',
                                            'repaired(fixed new parts)' => 'repaired(fixed new parts)',
                                            'Replaced with new item' => 'Replaced with new item',
                                            'Replaced with new other item' => 'Replaced with new other item',
                                            'Return in same condition(Warranty Void)' => 'Return in same condition(Warranty Void)',
                                            'Return in same condition(No problem)' => 'Return in same condition(No problem)',
                                        ])
                                        ->reactive()
                                         ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state !== 'repaired(fixed new parts)') {
                                                $set('replaced_part', null);
                                            }
                                            if ($state !== 'Replaced with new other item') {
                                                $set('replaced_product', null);
                                            }
                                        })
                                        ->placeholder('Select solution'),
                                        Select::make('replaced_part')
                                        ->multiple()
                                        ->label('Replaced Parts')
                                        ->options(function (callable $get) {
                                            $productId = $get('../../product_id');

                                            if (!$productId) return [];

                                            $product = \App\Models\Product::with('category')->find($productId);

                                            if (!$product) return [];

                                            return \App\Models\Part::all()
                                                ->filter(fn($part) => in_array($product->id, $part->product_id ?? []))
                                                ->pluck('name', 'id');
                                        })
                                        ->searchable()
                                        ->reactive()
                                        ->visible(fn(callable $get) => $get('solution') === 'repaired(fixed new parts)')
                                        ->dehydrated(fn(callable $get) => $get('solution') === 'repaired(fixed new parts)')
                                        ->placeholder('Select replaced parts'),
                                        Select::make('replaced_product')
                                            ->label('Replaced Product')
                                            ->options(
                                                \App\Models\Product::all()->pluck('name', 'id') // or apply filters if needed
                                            )
                                            ->searchable()
                                            ->reactive()
                                            ->visible(fn(callable $get) => $get('solution') === 'Replaced with new other item')
                                            ->dehydrated(fn(callable $get) => $get('solution') === 'Replaced with new other item')
                                            ->placeholder('Select replaced product'),
                                        TextInput::make('remarks')->nullable(),
                                    ]),
                            ]),
                        ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    TextColumn::make('date'),
                    TextColumn::make('user.name'),
                    TextColumn::make('mainstatus'),
                    TextColumn::make('invoice_id'),
                ]),
                Panel::make([
                    Split::make([
                        Stack::make([
                            ToggleColumn::make('sendbycus'),
                            TextColumn::make('sendbycus')->formatStateUsing(function ($state, $record){
                                return "Send By Customer : {$state}";
                            })
                        ]),
                        Stack::make([
                            ToggleColumn::make('recbycomp'),
                            TextColumn::make('recbycomp')->formatStateUsing(function ($state, $record){
                                return "Received By Us : {$state}";
                            })
                        ]),
                        Stack::make([
                            ToggleColumn::make('sendbackbycomp'),
                            TextColumn::make('sendbackbycomp')->formatStateUsing(function ($state, $record){
                                return "Sent Back By Us: {$state}";
                            })
                        ]),
                        Stack::make([
                            ToggleColumn::make('recbycus'),
                            TextColumn::make('recbycus')->formatStateUsing(function ($state, $record){
                                return "Received By Customer : {$state}";
                            })
                        ])
                    ])
                ])->collapsible()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDamages::route('/'),
            'create' => Pages\CreateDamage::route('/create'),
            'edit' => Pages\EditDamage::route('/{record}/edit'),
        ];
    }
}
