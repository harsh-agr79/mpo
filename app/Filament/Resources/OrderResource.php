<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogRelationManagerResource\RelationManagers\ActivityLogsRelationManager;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\SalesReturnResource\RelationManagers\OrderLogsRelationManager;
use App\Models\Order;
use App\Models\User;
use App\Models\Admin;
use App\Services\OrderExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Colors\Color;
use Carbon\Carbon;
use Closure;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Infolists\Components\Section;
use Filament\Notifications\Notification;
use Filament\Forms\Components\{TextInput, DatePicker, DateTimePicker, Textarea, Select, Toggle};
use Filament\Tables\Columns\{ColorColumn, CheckboxColumn, ToggleColumn, TextColumn, BooleanColumn, DateTimeColumn};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    // protected static ?string $title = '';

    protected static ?string $navigationGroup = 'Orders';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('discount')
                    ->numeric()
                    ->label('Discount')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($record, $component, $state) {
                        $record->{$component->getName()} = $state;
                        $record->save();
                        Notification::make()
                            ->title('Discount Updated')
                            ->success()
                            ->send();
                    }),
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
                TextColumn::make('orderid')
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
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                ViewAction::make()->size('xl')
                    ->label('')
                    ->modalHeading(fn($record) => 'ORDER: ' . ucfirst($record->orderid))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Section::make()
                            ->schema([

                                TextEntry::make('orderid')->label('ORDER ID'),
                                TextEntry::make('date')->label('DATE'),
                                TextEntry::make('custom_nep_date')
                                    ->label('NEPALI DATE')
                                    ->getStateUsing(fn($record) => ($record->nepyear && $record->nepmonth) ? "{$record->nepyear}/{$record->nepmonth}" : '-'),
                                TextEntry::make('user.name')->label('USER'),
                                TextEntry::make('net_total')->label('NET TOTAL')->money('NPR'),
                                TextEntry::make('discount')->label('DISCOUNT')->formatStateUsing(fn($record) => $record->discount . '%')->default(0),
                                TextEntry::make('total')->label('TOTAL')->money('NPR'),
                                TextEntry::make('mainstatus')->label('MAIN STATUS'),
                                TextEntry::make('clnstatus')->label('CLEAN STATUS')->default('N/A'),
                                TextEntry::make('delivered_at')->label('DELIVERED AT')->default('N/A'),
                                TextEntry::make('recieved_at')->label('RECEIVED AT')->default('N/A'),
                                TextEntry::make('othersname')->label('OTHERS')->default('-'),
                                TextEntry::make('cartoons')->label('CARTOONS')->default('-'),
                                TextEntry::make('user_remarks')->label('REMARKS')->markdown()->default('-'),
                                TextEntry::make('transport')->label('TRANSPORT')->markdown()->default('-'),
                                TextEntry::make('created_at')->label('CREATED AT')->dateTime('Y-m-d H:i'),
                                TextEntry::make('updated_at')->label('UPDATED AT')->dateTime('Y-m-d H:i'),
                                TextEntry::make('deleted_at')
                                    ->label('DELETED AT')
                                    ->dateTime('Y-m-d H:i')
                                    ->visible(fn($record) => filled($record->deleted_at)),
                                RepeatableEntry::make('items')
                                    ->label('ORDER ITEMS')
                                    ->schema([
                                        TextEntry::make('product.name')->label('PRODUCT'), // You can change this to product name if related
                                        TextEntry::make('quantity')->label('QTY'),
                                        TextEntry::make('price')->label('PRICE')->money('NPR'),
                                        TextEntry::make('actualprice')->label('ACTUAL PRICE')->money('NPR'),
                                        TextEntry::make('status')->label('STATUS'),
                                        TextEntry::make('offer')->label('OFFER')->default('-')
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->visible(fn($record) => $record->items->isNotEmpty()),
                                RepeatableEntry::make('materials')
                                    ->label('ORDER MATERIALS')
                                    ->schema([
                                        TextEntry::make('material.name')->label('MATERIAL'),
                                        TextEntry::make('quantity')->label('QUANTITY'),
                                        TextEntry::make('status')->label('STATUS'),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->visible(fn($record) => $record->materials->isNotEmpty()),

                            ])
                            ->columns(2),
                    ]),
                Action::make('generate_ind_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function (Order $record) {
                        // Make sure related data is loaded
                        $record->load([
                            'items.product', // load order items and their product info
                        ]);

                        $pdf = Pdf::loadView('pdf.order', ['order' => $record]);

                        return response()->streamDownload(
                            fn() => print ($pdf->output()),
                            'order-' . $record->id . '.pdf'
                        );
                    }),
                Action::make('download_png')
                    ->label('PNG')
                    ->icon('heroicon-o-photo')
                    ->color('success')
                    ->url(fn(Order $record) => route('png.order', $record))
                    ->openUrlInNewTab(),
                Action::make('download_png_with_image')
                    ->label('PNG with Product Images')
                    ->icon('heroicon-o-photo')
                    ->color('warning')
                    ->url(fn(Order $record) => route('png.orderImg', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('generate_bulk_pdf')
                        ->label('Export Orders PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function (Collection $records) {
                            $orders = $records->load(['items.product']); // eager load relations
                
                            $pdf = Pdf::loadView('pdf.orders', ['orders' => $orders]);

                            $filename = 'orders-' . now()->format('Ymd_His') . '.pdf';
                            $path = storage_path("app/public/{$filename}");

                            file_put_contents($path, $pdf->output());

                            return response()->download($path)->deleteFileAfterSend(true);
                        })
                        ->deselectRecordsAfterCompletion()
                    ,
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ])

            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Main', [
                RelationManagers\ItemsRelationManager::class,
                RelationManagers\OrderMaterialsRelationManager::class,
                RelationManagers\RemarksRelationManager::class,
            ]),
            OrderLogsRelationManager::class
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
