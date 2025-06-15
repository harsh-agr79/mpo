<?php

namespace App\Filament\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;

use Filament\Tables;
use App\Models\Order;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Contracts\HasTable;
// use Filament\Tables;
 use Filament\Tables\Columns\ {
        ColorColumn, CheckboxColumn, ToggleColumn, TextColumn, BooleanColumn, DateTimeColumn}
        ;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\TrashedFilter;

class PendingOrders extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.pending-orders';

    protected static ?string $title = '';
    protected static ?string $navigationLabel = 'Pending Orders';
    protected static ?string $navigationGroup = 'Orders';
    protected static ?string $navigationIcon = 'heroicon-o-clock'; // optional
    protected static ?int $navigationSort = 1;

    public function table(Table $table): Table
    {
        return $table
                ->query(Order::query()->where('mainstatus', 'pending')->whereNotNull('seenby'))
                ->defaultSort( 'created_at', 'desc' )
                ->columns( [
                  TextColumn::make( 'mainstatus' )
                    ->toggleable()
                    ->label( '' )
                    ->formatStateUsing( function ( ?string $state, $record ): string {
                        $colorMap = [
                            'pending'   => 'oklch(74.6% 0.16 232.661)',
                            'approved'  => 'oklch(82.8% 0.189 84.429)',
                            'rejected'  => 'red',
                        ];

                        // If seenby is null, override color to your 'not seen' color
                        if ( $record->seenby === null ) {
                            $color = 'oklch(55.3% 0.013 58.071)';
                            // Your 'not seen' color
                        } else {
                            $color = $colorMap[ $state ] ?? 'gray';
                        }

                        if($record->clnstatus === 'packing' && $record->mainstatus === 'approved') {
                            $color = 'purple';
                        } elseif($record->clnstatus === 'delivered' && $record->mainstatus === 'approved') {
                            $color = $colorMap[ $state ] ?? 'gray';
                        }

                        if($record->clnstatus === 'delivered' && $record->delivered_at !== null) {
                            $color = 'green';
                        } elseif($record->clnstatus === 'delivered' && $record->mainstatus === 'approved') {
                            $color = $colorMap[ $state ] ?? 'gray';
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
                    ->description( fn ( $record ) => $record->date->format( 'm-d H:i' ) ),
                    // ->toggleable()

                TextColumn::make( 'user.name' )->description(fn ($record) => $record->user->shop_name)
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                ->searchable(),
                // ->description(fn ( $record ) => $record->orderid),
                TextColumn::make( 'orderid' )
                    ->size(TextColumn\TextColumnSize::ExtraSmall)
                ->toggleable(),
                ToggleColumn::make('clnstatus')
                ->label('Pack')
                ->disabled(fn ($record, $state) => $record->mainstatus === 'approved' && $record->clnstatus !== 'delivered'? false : true)
                ->afterStateUpdated(function ($record, $state) {
                    if($state === true) {
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
                ->disabled(fn ($record, $state) => $record->mainstatus === 'approved' && ($record->clnstatus === 'packing' || $record->clnstatus === 'delivered') ? false : true)
                ->afterStateUpdated(function ($record, $state) {
                    if($state === true) {
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
                TextColumn::make( 'seenby' )
                ->size(TextColumn\TextColumnSize::ExtraSmall)
                ->label( 'Seen By' )
                ->badge()
                 ->toggleable()
                ->formatStateUsing( function ( $state, $record ) {
                    return $record->seenby === null ? 'NOT SEEN' : optional( $record->seenAdmin )->name;
                }
            )
            ->color( fn ( $state ) => $state === 'NOT SEEN' ? 'danger' : 'success' )
        ] )
         ->recordClasses(function ($record) {
           
            if ($record->seenby === null) return '';

            if ($record->mainstatus === 'pending') return 'bg-status-pending';
            if ($record->mainstatus === 'rejected') return 'bg-status-rejected';
            if ($record->mainstatus === 'approved' && $record->clnstatus === null) return 'bg-status-approved';
            if ($record->clnstatus === 'packing' && $record->mainstatus === 'approved') return 'bg-status-packing';
            if ($record->clnstatus === 'delivered' && $record->delivered_at !== null) return 'bg-status-delivered';

            return '';
        })

        ->filters( [
            Tables\Filters\TrashedFilter::make(),
        ] )
        ->actions( [
            // Tables\Actions\EditAction::make(),
            // Tables\Actions\DeleteAction::make(),
            Tables\Actions\ForceDeleteAction::make(),
            Tables\Actions\RestoreAction::make(),
                ActionGroup::make([
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
                    Action::make('generate_ind_pdf_with_images')
                        ->label('PDF with Images')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('warning')
                        ->action(function ($record) {
                            // Eager load relations manually
                            $record->load(['items.product']);

                            // Use an array of one order to reuse the same Blade template
                            $pdf = Pdf::loadView('pdf.orderImg', ['orders' => collect([$record])]);

                            $filename = 'order-' . $record->orderid . '.pdf';
                            $path = storage_path("app/public/{$filename}");

                            file_put_contents($path, $pdf->output());

                            return response()->download($path)->deleteFileAfterSend(true);
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
                ])->icon('heroicon-m-ellipsis-horizontal')
            ] )
        ->bulkActions( [
            Tables\Actions\BulkActionGroup::make( [
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
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
                    BulkAction::make('generate_bulk_pdf_with_images')
                        ->label('Export PDF with Images')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('warning')
                        ->action(function (Collection $records) {
                            $orders = $records->load(['items.product']); // eager load relations
                
                            $pdf = Pdf::loadView('pdf.orderImg', ['orders' => $orders]);

                            $filename = 'orders-' . now()->format('Ymd_His') . '.pdf';
                            $path = storage_path("app/public/{$filename}");

                            file_put_contents($path, $pdf->output());

                            return response()->download($path)->deleteFileAfterSend(true);
                        })
                        ->deselectRecordsAfterCompletion()
                    ,
            ] )

        ] )
         ->recordUrl(fn (Order $record) => route('filament.admin.resources.orders.edit', ['record' => $record->getKey()]));
    }

     public static function getNavigationBadge(): ?string
    {
        return (string) Order::where('mainstatus', 'pending')->whereNotNull('seenby')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
