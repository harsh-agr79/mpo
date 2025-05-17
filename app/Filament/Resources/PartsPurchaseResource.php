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
use PartsPurchaseLogsRelationManager;
use DiscoveryDesign\FilamentGaze\Forms\Components\GazeBanner;

class PartsPurchaseResource extends Resource
{
    protected static ?string $model = PartsPurchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationGroup = 'Purchase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                  GazeBanner::make()
                ->pollTimer(1)
                ->lock()
                ->canTakeControl(fn() => auth()->user()?->hasRole('Admin'))
                ->hideOnCreate()
                ->columnSpanFull(),
                DatePicker::make('date')
                    ->default(today())
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, $state, $context) {
                        if ($context === 'create') {
                            $set('invoice_id', getNepaliInvoiceId($state));
                        }
                    }),
                TextInput::make('invoice_id')
                    ->default(
                        fn(Get $get, $context) =>
                        $context === 'create'
                        ? getNepaliInvoiceId($get('date') ?? today()->format('Y-m-d'))
                        : null
                    )
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
                TextColumn::make('date')
                    ->label('Date (A.D.)')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('nepali_date')
                    ->label('Date (B.S.)')
                    ->getStateUsing(fn($record) => getNepaliDate($record->date))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('invoice_id')->sortable()->searchable()->label('Invoice ID'),
                TextColumn::make('items_sum_quantity')
                    ->label('Total Quantity')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make()->size('xl')
                    ->label('')
                    ->modalHeading(fn($record) => 'Part Purchase: ' . ucfirst($record->invoice_id))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Section::make()
                            ->schema([

                                TextEntry::make('invoice_id')->label('INVOICE ID'),
                                TextEntry::make('date')->label('DATE'),
                                RepeatableEntry::make('items')
                                    ->label('Purchase Items')
                                    ->columnSpanFull()
                                    ->schema([
                                        TextEntry::make('part.name')->label('Part Name'),
                                        TextEntry::make('quantity')->label('Quantity'),
                                        TextEntry::make('voucher')->label('Voucher'),
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
            PartsPurchaseLogsRelationManager::class,
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
