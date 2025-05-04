<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('expense_date')
                    ->label('Expense DateTime')
                    ->dateTime('Y-m-d H:i:s'),
                TextColumn::make('expense_date_nepali')
                    ->label('Expense Date (B.S.)')
                    ->getStateUsing(fn ($record) => getNepaliDate($record->expense_date)),
                TextColumn::make('amount')
                    ->money('npr'),
                TextColumn::make('particular')
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
