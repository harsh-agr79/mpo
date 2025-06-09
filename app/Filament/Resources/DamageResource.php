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
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DateTimePicker;
use Closure;
use Filament\Forms\Components\Grid;

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
                DateTimePicker::make('sendbycus'),
                DateTimePicker::make('recbycomp'),
                DateTimePicker::make('sendbackbycomp'),
                DateTimePicker::make('recbycus'),
                Textarea::make('remarks')->columnSpanFull(),
                Repeater::make('damageItems') ->relationship('damageItems')->columnSpanFull()
                        ->schema([
                            Grid::make(4)->schema([
                                Select::make('product_id')
                            ->relationship('product', 'name')
                            ->options(Product::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                            TextInput::make('quantity')->numeric()->required(),
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
                                ->schema([
                                    Grid::make(4)->schema([
                                   Hidden::make('product_id')
                                    ->default(fn (callable $get) => $get('../../product_id'))
                                    ->afterStateHydrated(fn (callable $set, callable $get) => $set('product_id', $get('../../product_id')))
                                    ->dehydrated()
                                    ->required(),
                                    TextInput::make('quantity')->numeric()->required(),
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

                                            return \App\Models\Part::query()
                                                ->whereJsonContains('product_id', $productId)
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
                //
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
