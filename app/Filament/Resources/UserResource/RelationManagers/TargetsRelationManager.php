<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TargetsRelationManager extends RelationManager
{
    protected static string $relationship = 'targets';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('gross_target')->numeric()->required()->inputMode('decimal'),
                TextInput::make('net_target')->numeric()->required()->inputMode('decimal'),
                DatePicker::make('start_date')->required()->displayFormat('Y-m-d'),
                DatePicker::make('end_date')->required()->displayFormat('Y-m-d'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('start_date')->date('Y-m-d')->sortable(),
                TextColumn::make('end_date')->date('Y-m-d')->sortable(),
                TextColumn::make('gross_target')->money('NPR')->sortable(),
                TextColumn::make('net_target')->money('NPR')->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
