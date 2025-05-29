<?php

namespace App\Filament\Resources\SalesReturnResource\RelationManagers;

use App\Models\ActivityLog;
use App\Models\Admin;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Builder;

class MaterialInvoiceLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'activityLogs';
    private function getActivityLogQuery(): Builder
    {
        $invoice = $this->getOwnerRecord();

        return ActivityLog::query()
            ->where(function ($query) use ($invoice) {
                $query->where(function ($q) use ($invoice) {
                    $q->where('table_name', 'material_invoices')
                        ->where('primary_key_value', $invoice->id);
                })->orWhere(function ($q) use ($invoice) {
                    $q->where('table_name', 'material_invoice_items')
                        ->whereIn('primary_key_value', function ($sub) use ($invoice) {
                            $sub->select('id')
                                ->from('material_invoice_items')
                                ->where('invoice_id', $invoice->invoice_id);
                        });
                });
            });
    }


    public function table(Table $table): Table
    {
        $purchase = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('operation')
            ->defaultSort('created_at', 'desc')
            ->query(
                $this->getActivityLogQuery()
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('DateTime (A.D.)')
                    ->sortable(),
                TextColumn::make('created_at_nep')
                    ->label('Date (B.S.)')
                    ->getStateUsing(fn($record) => getNepaliDate($record->created_at))
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('table_name')
                    ->label('Table')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('operation')
                    ->label('Action')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('operation')->label('Action')
                    ->searchable()
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),
                SelectFilter::make('admin_id')->label('User')
                    ->searchable()
                    ->options(Admin::all()->pluck('name', 'id'))
                ,
            ])
            ->actions([
                ViewAction::make()->size('xl')
                    ->label('')
                    ->modalHeading(fn($record) => 'Activity: ' . ucfirst($record->operation))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Section::make()
                            ->schema([

                                TextEntry::make('operation')->label('OPERATION'),
                                TextEntry::make('table_name')->label('TABLE'),
                                TextEntry::make('user.name')->label('USER')->default('System'),
                                TextEntry::make('created_at')->label('TIME')->dateTime(),
                            ])
                            ->columns(2),
                        KeyValueEntry::make('old_data')
                            ->label('Old Data')
                            ->state(function ($record) {
                                $data = is_array($record->old_data)
                                    ? $record->old_data
                                    : json_decode($record->old_data, true) ?? [];

                                // Recursively stringify any non-string values
                                return collect($data)->map(function ($value) {
                                    return is_array($value)
                                        ? json_encode($value, JSON_UNESCAPED_UNICODE)
                                        : (string) $value;
                                })->toArray();
                            })
                            ->hidden(fn($record) => empty($record->old_data)),

                        KeyValueEntry::make('new_data')
                            ->label('New Data')
                            ->state(function ($record) {
                                $data = is_array($record->new_data)
                                    ? $record->new_data
                                    : json_decode($record->new_data, true) ?? [];

                                return collect($data)->map(function ($value) {
                                    return is_array($value)
                                        ? json_encode($value, JSON_UNESCAPED_UNICODE)
                                        : (string) $value;
                                })->toArray();
                            })
                            ->hidden(fn($record) => empty($record->new_data)),
                    ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
