<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogRelationManagerResource\RelationManagers\ActivityLogsRelationManager;
use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Transactions';

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
                DateTimePicker::make('expense_date')
                    ->label('Expense Date')
                    ->required()
                    ->displayFormat('Y-m-d H:i:s'),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->inputMode('decimal'),
                TextInput::make('particular')
                    ->nullable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_date')
                    ->label('Expense DateTime')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                TextColumn::make('expense_date_nepali')
                    ->label('Expense Date (B.S.)')
                    ->sortable()
                    ->getStateUsing(fn($record) => getNepaliDate($record->expense_date))
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('npr')
                    ->sortable(),
                TextColumn::make('particular')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make()
                    ->label('')
                    ->modalHeading(fn($record) => 'Expense: ' . ucfirst($record->id))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Section::make()
                            ->schema([

                                TextEntry::make('user.name')->label('USER'),
                                TextEntry::make('expense_date')->label('EXPENSE DATE'),
                                TextEntry::make('amount')->label('AMOUNT')->money('npr'),
                                TextEntry::make('particular')->label('PARTICULAR'),
                                TextEntry::make('created_at')->label('CREATED_AT'),
                                TextEntry::make('updated_at')->label('UPDATED_AT'),
                                TextEntry::make('deleted_at')->label('DELETED_AT')->visible(fn($record) => filled($record->deleted_at)),
                            ])
                            ->columns(2),
                    ]),
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label(''),
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
            ActivityLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
