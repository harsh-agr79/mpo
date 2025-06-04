<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesReturnResource\Pages;
use App\Filament\Resources\SalesReturnResource\RelationManagers;
use App\Models\SalesReturn;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Colors\Color;
use Carbon\Carbon;
use Closure;
use App\Filament\Resources\SalesReturnResource\RelationManagers\SalesReturnLogsRelationManager;

use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Infolists\Components\Section;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ViewAction;
use Filament\Infolists\Components\{TextEntry, RepeatableEntry};
use Filament\Forms\Components\{TextInput, DatePicker, DateTimePicker, Textarea, Select, Toggle};
use Filament\Tables\Columns\{ColorColumn, CheckboxColumn, ToggleColumn, TextColumn, BooleanColumn, DateTimeColumn};

class SalesReturnResource extends Resource
{
    protected static ?string $model = SalesReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $navigationGroup = 'Sales Return';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->relationship(name: 'user', titleAttribute: 'name')
                    ->label('Customer')
                    ->searchable()
                    ->options(User::all()->pluck('name', 'id'))
                    ->required(),
                DatePicker::make('date')
                    ->label('Order Date')
                    ->default(now()) // ⬅️ sets today's date
                    ->required(),
                TextInput::make('discount')
                    ->numeric()
                    ->label('Discount')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($record, $component, $state) {
                        $record->{$component->getName()} = $state;
                        $record->save();
                        Notification::make()
                            ->title('Discount Updated')
                            ->success()
                            ->send();
                    }),
                TextInput::make('remarks'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('nepali_date')
                    ->label('Date (B.S.)')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->getStateUsing(fn($record) => getNepaliDate($record->date))
                    // ->sortable()
                    ->description(fn($record) => $record->date->format('m-d H:i')),
                // ->toggleable()

                TextColumn::make('user.name')->description(fn($record) => $record->user->shop_name)
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->searchable(),
                // ->description(fn ( $record ) => $record->orderid),
                TextColumn::make('return_id')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->toggleable(),
                TextColumn::make('net_total')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make()->size('xl')
                    ->label('')
                    ->modalHeading(fn($record) => 'RETURN: ' . ucfirst($record->return_id))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Section::make()
                            ->schema([
                                TextEntry::make('return_id')->label('RETURN ID'),
                                TextEntry::make('date')->label('DATE'),
                                TextEntry::make('custom_nep_date')
                                    ->label('NEPALI DATE')
                                    ->getStateUsing(fn($record) => ($record->nepyear && $record->nepmonth) ? "{$record->nepyear}/{$record->nepmonth}" : '-'),
                                TextEntry::make('user.name')->label('USER'),
                                TextEntry::make('net_total')->label('NET TOTAL')->money('NPR'),
                                TextEntry::make('discount')->label('DISCOUNT')->formatStateUsing(fn($record) => $record->discount . '%')->default(0),
                                TextEntry::make('total')->label('TOTAL')->money('NPR'),
                                TextEntry::make('remarks')->label('REMARKS')->markdown()->default('-'),
                                TextEntry::make('created_at')->label('CREATED AT')->dateTime('Y-m-d H:i'),
                                TextEntry::make('updated_at')->label('UPDATED AT')->dateTime('Y-m-d H:i'),
                                TextEntry::make('deleted_at')
                                    ->label('DELETED AT')
                                    ->dateTime('Y-m-d H:i')
                                    ->visible(fn($record) => filled($record->deleted_at)),

                                RepeatableEntry::make('items')
                                    ->label('RETURN ITEMS')
                                    ->schema([
                                        TextEntry::make('product.name')->label('PRODUCT'),
                                        TextEntry::make('quantity')->label('QUANTITY'),
                                        TextEntry::make('price')->label('PRICE')->money('NPR'),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->visible(fn($record) => $record->items->isNotEmpty()),
                            ])
                            ->columns(2),
                    ]),
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
            RelationManagers\ItemsRelationManager::class,
            SalesReturnLogsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesReturns::route('/'),
            'create' => Pages\CreateSalesReturn::route('/create'),
            'edit' => Pages\EditSalesReturn::route('/{record}/edit'),
        ];
    }
}
