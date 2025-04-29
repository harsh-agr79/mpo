<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TargetResource\Pages;
use App\Filament\Resources\TargetResource\RelationManagers;
use App\Models\Target;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PhpParser\Node\Stmt\Label;

class TargetResource extends Resource
{
    

    protected static ?string $model = Target::class;

    protected static ?string $navigationIcon = 'heroicon-o-cursor-arrow-ripple';

    protected static ?string $navigationGroup = 'Transactions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->options(User::all()->pluck('name', 'id'))
                    ->required(),
                TextInput::make('gross_target')
                    ->label('Gross Target')
                    ->numeric()
                    ->inputMode('decimal')
                    ->required(),
                TextInput::make('net_target')
                    ->label('Net Target')
                    ->numeric()
                    ->inputMode('decimal')
                    ->required(),
                DatePicker::make('start_date')
                    ->required()
                    ->label('Start Date')
                    ->displayFormat('Y-m-d'),
                DatePicker::make('end_date')
                    ->label('End Date')
                    ->displayFormat('Y-m-d'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('gross_target')
                    ->label('Gross Target')
                    ->money('npr')
                    ->sortable(),
                TextColumn::make('net_target')
                    ->label('Net Target')
                    ->money('npr')
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListTargets::route('/'),
            'create' => Pages\CreateTarget::route('/create'),
            'edit' => Pages\EditTarget::route('/{record}/edit'),
        ];
    }
}
