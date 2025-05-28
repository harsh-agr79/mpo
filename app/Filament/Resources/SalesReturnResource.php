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
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Forms\Components\ {TextInput, DatePicker, DateTimePicker, Textarea, Select, Toggle};
use Filament\Tables\Columns\ {ColorColumn, CheckboxColumn, ToggleColumn, TextColumn, BooleanColumn, DateTimeColumn};

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
                DatePicker::make( 'date' )
                        ->label( 'Order Date' )
                        ->default( now() ) // ⬅️ sets today's date
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
            ->defaultSort( 'date', 'desc' )
            ->columns([
                TextColumn::make('nepali_date')
                    ->label('Date (B.S.)')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->getStateUsing(fn($record) => getNepaliDate($record->date))
                    // ->sortable()
                    ->description( fn ( $record ) => $record->date->format( 'm-d H:i' ) ),
                    // ->toggleable()

                TextColumn::make( 'user.name' )->description(fn ($record) => $record->user->shop_name)
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                ->searchable(),
                // ->description(fn ( $record ) => $record->orderid),
                TextColumn::make( 'return_id' )
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                ->toggleable(),
                TextColumn::make( 'net_total' )
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                ->toggleable(),
            ])
            ->filters([
                 Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                  Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
               Tables\Actions\BulkActionGroup::make( [
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
             ] ),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
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
