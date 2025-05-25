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

class OrderMaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'materials';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('material_id')
                ->label('Material')
                ->searchable()
                ->required()
                ->reactive()
                ->options(function (callable $get, $livewire) {
                    $parentRecord = $livewire->ownerRecord ?? null;

                    $alreadyAddedProductIds = collect($parentRecord?->orderMaterials ?? [])
                        ->pluck('material_id')
                        ->filter() // Remove nulls
                        ->toArray();

                    return \App\Models\Material::query()
                        ->when(!empty($alreadyAddedProductIds), fn ($query) =>
                            $query->whereNotIn('id', $alreadyAddedProductIds)
                        )
                        ->pluck('name', 'id')
                        ->toArray();
                }),

                TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->required(),

                 Hidden::make('status')->default('pending'),
                 Hidden::make('orderid')->default(fn ($livewire) => $livewire->ownerRecord->orderid),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('orderid')
            ->poll('3s')
            ->columns([
                Tables\Columns\ImageColumn::make('material.image')
                    ->label('Image')
                    ->square()
                    ->width(50)
                    ->height(50)
                    ->sortable()
                    ->toggleable(),
               Tables\Columns\TextColumn::make('material.name')
                    ->label('Product Name')
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextInputColumn::make('quantity'),
                Tables\Columns\SelectColumn::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
