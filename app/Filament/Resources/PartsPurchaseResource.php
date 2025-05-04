<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartsPurchaseResource\Pages;
use App\Filament\Resources\PartsPurchaseResource\RelationManagers;
use App\Models\Part;
use App\Models\PartsPurchase;
use Closure;
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

class PartsPurchaseResource extends Resource
{
    protected static ?string $model = PartsPurchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationGroup = 'Transactions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->default(today())
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('invoice_id', getNepaliInvoiceId($state));
                    }),
                TextInput::make('invoice_id')
                    ->default(fn(Get $get) => getNepaliInvoiceId($get('date') ?? today()->format('Y-m-d')))
                    ->disabled()
                    ->dehydrated(),
                Repeater::make('items')->relationship()->schema([
                    Select::make('part_id')->relationship('part', 'name')->searchable()->options(Part::all()->pluck('name', 'id'))->required(),
                    Select::make('voucher')->searchable()->options([
                        'purchase' => 'Purchase',
                        'sales' => 'Sales',
                        'loss' => 'Loss',
                        'found' => 'Found',
                        'refurbish' => 'Refurbish',
                        'office_dmg_use' => 'Office Damage Use'
                    ])->required(),
                    TextInput::make('quantity')->numeric()->required(),
                ])->columnSpanFull()->columns(3)->minItems(1)->createItemButtonLabel('Add Item'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_id')->sortable()->searchable()->label('Invoice ID'),
                TextColumn::make('date')
                    ->label('Date (A.D.)')
                    ->date('Y-m-d'),
                TextColumn::make('nepali_date')
                    ->label('Date (B.S.)')
                    ->getStateUsing(fn($record) => getNepaliDate($record->date)),
                TextColumn::make('items_sum_quantity')
                    ->label('Total Quantity'),
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
            'index' => Pages\ListPartsPurchases::route('/'),
            'create' => Pages\CreatePartsPurchase::route('/create'),
            'edit' => Pages\EditPartsPurchase::route('/{record}/edit'),
        ];
    }

    public static function beforeCreate($record)
    {
        $record->invoice_id = getNepaliInvoiceId();
    }
}
