<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\{TextInput, Select, Hidden};
use Filament\Tables\Columns\{TextColumn, BadgeColumn};

class RemarksRelationManager extends RelationManager
{
    protected static string $relationship = 'remarks';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('remark')
                    ->label('Remark')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Hidden::make('remarks_by')
                    ->default(fn ($livewire) => auth()->user()->id),
                Hidden::make('orderid')->default(fn ($livewire) => $livewire->ownerRecord->orderid),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('orderid')
            ->defaultSort('created_at', 'desc')
            ->poll('2s')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('admin.name')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                ,
                Tables\Columns\TextColumn::make('remark')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                ,
                Tables\Columns\TextColumn::make('created_at')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                ,
            ])
            ->filters([
                //
            ])
             ->emptyStateHeading('')
            ->emptyStateDescription('')
            ->emptyStateIcon(null)
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Add Remark')->icon('heroicon-o-plus-circle'),
            ])
           
                ->actions([
                    Tables\Actions\EditAction::make()
                        ->disabled(fn ($record): bool => (int) $record->remarks_by !== (int) auth()->id()),

                    Tables\Actions\DeleteAction::make()
                        ->disabled(fn ($record): bool => (int) $record->remarks_by !== (int) auth()->id()),
                ])
        
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     // Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
