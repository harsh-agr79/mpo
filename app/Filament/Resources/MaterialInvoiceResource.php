<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogRelationManagerResource\RelationManagers\ActivityLogsRelationManager;
use App\Filament\Resources\MaterialInvoiceResource\Pages;
use App\Filament\Resources\MaterialInvoiceResource\RelationManagers;
use App\Filament\Resources\SalesReturnResource\RelationManagers\MaterialInvoiceLogsRelationManager;
use App\Models\MaterialInvoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use App\Models\User;
use App\Models\Admin;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
// use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Colors\Color;
use Carbon\Carbon;
use Closure;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Forms\Components\{TextInput, DatePicker, DateTimePicker, Textarea, Select, Toggle};
use Filament\Tables\Columns\{ColorColumn, CheckboxColumn, ToggleColumn, TextColumn, BooleanColumn, DateTimeColumn};
// use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;

class MaterialInvoiceResource extends Resource
{
    protected static ?string $model = MaterialInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Materials';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Invoice Specifics')
                    ->schema([
                        Select::make('user_id')
                            ->relationship(name: 'user', titleAttribute: 'name')
                            ->label('Customer')
                            ->searchable()
                            ->options(User::all()->pluck('name', 'id'))
                            ->required(),
                        DatePicker::make('date')
                            ->label('Order Date')
                            ->default(now()) // ⬅️ sets today's date
                            ->required(),
                    ])->columns(2),
                Section::make('Shippent Details')
                    ->schema([
                        TextInput::make('cartoons'),
                        TextInput::make('transport')
                    ])->collapsible()
                    ->persistCollapsed()->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('mainstatus')
                    ->toggleable()
                    ->label('')
                    ->formatStateUsing(
                        function (?string $state, $record): string {
                            $colorMap = [
                                'pending' => 'oklch(74.6% 0.16 232.661)',
                                'approved' => 'oklch(82.8% 0.189 84.429)',
                                'rejected' => 'red',
                            ];

                            // If seenby is null, override color to your 'not seen' color
                            if ($record->seenby === null) {
                                $color = 'oklch(55.3% 0.013 58.071)';
                                // Your 'not seen' color
                            } else {
                                $color = $colorMap[$state] ?? 'gray';
                            }

                            if ($record->clnstatus === 'packing' && $record->mainstatus === 'approved') {
                                $color = 'purple';
                            } elseif ($record->clnstatus === 'delivered' && $record->mainstatus === 'approved') {
                                $color = $colorMap[$state] ?? 'gray';
                            }

                            if ($record->clnstatus === 'delivered' && $record->delivered_at !== null) {
                                $color = 'green';
                            } elseif ($record->clnstatus === 'delivered' && $record->mainstatus === 'approved') {
                                $color = $colorMap[$state] ?? 'gray';
                            }

                            return "<div title='{$state}' style='width: 0.5rem; height: 1.5rem; background-color: {$color};'></div>";
                        }
                    )
                    ->html(),
                TextColumn::make('nepali_date')
                    ->label('Date (B.S.)')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->getStateUsing(fn($record) => getNepaliDate($record->date))
                    // ->sortable()
                    ->description(fn($record) => $record->date->format('m-d H:i')),
                // ->toggleable()

                TextColumn::make('user.name')->description(fn($record) => $record->user->shop_name)
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->searchable(),
                // ->description(fn ( $record ) => $record->orderid),
                TextColumn::make('invoice_id')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->toggleable(),
                ToggleColumn::make('clnstatus')
                    ->label('Pack')
                    ->disabled(fn($record, $state) => $record->mainstatus === 'approved' && $record->clnstatus !== 'delivered' ? false : true)
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state === true) {
                            $record->update([
                                'clnstatus' => 'packing',
                                'clntime' => time(),
                            ]);
                        } else {
                            $record->update([
                                'clnstatus' => null,
                                'clntime' => null,
                            ]);
                        }
                    })
                    ->toggleable()
                ,
                ToggleColumn::make('delivered_at')
                    ->label('Delivered')
                    ->disabled(fn($record, $state) => $record->mainstatus === 'approved' && ($record->clnstatus === 'packing' || $record->clnstatus === 'delivered') ? false : true)
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state === true) {
                            $record->update([
                                'clnstatus' => 'delivered',
                                'delivered_at' => now(),
                            ]);
                        } else {
                            $record->update([
                                'clnstatus' => 'packing',
                                'delivered_at' => null,
                            ]);
                        }
                    })
                    ->toggleable()
                ,
                // TextColumn::make( 'mainstatus' )->limit( 20 ),
                TextColumn::make('seenby')
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                    ->label('Seen By')
                    ->badge()
                    ->toggleable()
                    ->formatStateUsing(
                        function ($state, $record) {
                            return $record->seenby === null ? 'NOT SEEN' : optional($record->seenAdmin)->name;
                        }
                    )
                    ->color(fn($state) => $state === 'NOT SEEN' ? 'danger' : 'success')
            ])
            ->recordClasses(function ($record) {

                if ($record->seenby === null)
                    return '';

                if ($record->mainstatus === 'pending')
                    return 'bg-status-pending';
                if ($record->mainstatus === 'rejected')
                    return 'bg-status-rejected';
                if ($record->mainstatus === 'approved' && $record->clnstatus === null)
                    return 'bg-status-approved';
                if ($record->clnstatus === 'packing' && $record->mainstatus === 'approved')
                    return 'bg-status-packing';
                if ($record->clnstatus === 'delivered' && $record->delivered_at !== null)
                    return 'bg-status-delivered';

                return '';
            })
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
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
            RelationManagers\ItemsRelationManager::class,
            MaterialInvoiceLogsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaterialInvoices::route('/'),
            'create' => Pages\CreateMaterialInvoice::route('/create'),
            'edit' => Pages\EditMaterialInvoice::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
