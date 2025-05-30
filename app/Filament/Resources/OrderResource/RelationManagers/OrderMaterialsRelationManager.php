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
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
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
            ->heading('Input')
            ->headerActions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('bulkApproveAll')
                        ->label('Approve All Items')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function () {
                            $items = $this->getOwnerRecord()->items;

                            foreach ($items as $record) {
                                $record->status = 'approved';
                                $record->save();
                            }
                        }),

                    Tables\Actions\Action::make('bulkRejectAll')
                        ->label('Reject All Items')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function () {
                            $items = $this->getOwnerRecord()->items;

                            foreach ($items as $record) {
                                $record->status = 'rejected';
                                $record->save();
                            }
                        }),

                    Tables\Actions\Action::make('bulkPendingAll')
                            ->label('Mark All as Pending')
                            ->icon('heroicon-o-clock')
                            ->color('warning')
                            ->action(function () {
                                $items = $this->getOwnerRecord()->items;

                                foreach ($items as $record) {
                                    $record->status = 'pending';
                                    $record->save();
                                }
                            }),
                ])
                ->label('Status Update')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('info')
                ->button(),
                Tables\Actions\CreateAction::make()->label('Add Input')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary'),
            ])
            ->emptyStateHeading('')
            ->emptyStateDescription('')
            ->emptyStateIcon('')
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
