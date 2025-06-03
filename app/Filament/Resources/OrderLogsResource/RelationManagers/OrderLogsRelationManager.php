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

class OrderLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'activityLogs';

    // private function getActivityLogQuery(): Builder
    // {
    //     $order = $this->getOwnerRecord();

    //     return ActivityLog::query()
    //         ->where(function ($query) use ($order) {
    //             $query->where(function ($q) use ($order) {
    //                 $q->where('table_name', 'orders')
    //                     ->where('primary_key_value', $order->id);
    //             })->orWhere(function ($q) use ($order) {
    //                 $q->where('table_name', 'order_items')
    //                     ->whereIn('primary_key_value', function ($sub) use ($order) {
    //                         $sub->select('id')
    //                             ->from('order_items')
    //                             ->where('orderid', $order->orderid);
    //                     });
    //             })->orWhere(function ($q) use ($order) {
    //                 $q->where('table_name', 'order_materials')
    //                     ->whereIn('primary_key_value', function ($sub) use ($order) {
    //                         $sub->select('id')
    //                             ->from('order_materials')
    //                             ->where('orderid', $order->orderid);
    //                     });
    //             });
    //         });
    // }

    private function getActivityLogQuery(): Builder
    {
        $order = $this->getOwnerRecord();

        return ActivityLog::query()
            ->where(function ($query) use ($order) {
                $query
                    ->where(function ($q) use ($order) {
                        $q->where('table_name', 'orders')
                            ->where('primary_key_value', $order->id);
                    })
                    ->orWhere(function ($q) use ($order) {
                        $q->where('table_name', 'order_items')
                            ->where(function ($q2) use ($order) {
                                $q2->where('old_data', 'like', '%orderid%' . $order->orderid . '%')
                                    ->orWhere('new_data', 'like', '%orderid%' . $order->orderid . '%');
                            });
                    })
                    ->orWhere(function ($q) use ($order) {
                        $q->where('table_name', 'order_materials')
                            ->where(function ($q2) use ($order) {
                                $q2->where('old_data', 'like', '%orderid%' . $order->orderid . '%')
                                    ->orWhere('new_data', 'like', '%orderid%' . $order->orderid . '%');
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
             ->defaultPaginationPageOption(1)
            ->paginated([1,5,10, 25, 50, 100, 'all'])
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
