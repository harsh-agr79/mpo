<?php

namespace App\Filament\Resources\ActivityLogRelationManagerResource\RelationManagers;

use App\Models\Admin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'activityLogs';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('operation')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('operation')
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
                ViewAction::make()
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
