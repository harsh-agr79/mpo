<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\User;
use App\Models\Admin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Colors\Color;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Forms\Components\ {
    TextInput, DatePicker, DateTimePicker, Textarea, Select, Toggle}
    ;
    use Filament\Tables\Columns\ {
        ColorColumn, CheckboxColumn, ToggleColumn, TextColumn, BooleanColumn, DateTimeColumn}
        ;
        use Illuminate\Database\Eloquent\SoftDeletingScope;
        use Filament\Tables\Columns\Layout\Stack;
        use Filament\Tables\Columns\Layout\Split;

        class OrderResource extends Resource {
            protected static ?string $model = Order::class;

            protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

             protected static ?string $navigationGroup = 'Orders';
          
            public static function form( Form $form ): Form {
                return $form
                ->schema( [
                     Select::make('user_id')
                    ->relationship(name: 'user', titleAttribute: 'name')
                    ->label('Customer')
                    ->searchable()
                    ->options(User::all()->pluck('name', 'id'))
                    ->required(),
                    DatePicker::make( 'date' )
                    ->label( 'Order Date' )
                    ->default( now() ) // ⬅️ sets today's date
                    ->required(),
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
                ] );
            }

            public static function table( Table $table ): Table {
                return $table
                ->defaultSort( 'created_at', 'desc' )
                ->columns( [
                    TextColumn::make( 'mainstatus' )
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
                    ->getStateUsing(fn($record) => getNepaliDate($record->date))
                    ->sortable(),
                    // ->toggleable()

                TextColumn::make( 'user.name' )->searchable(),
                // ->description(fn ( $record ) => $record->orderid),
                TextColumn::make( 'orderid' ),
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
                }),
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
                }),
                // TextColumn::make( 'mainstatus' )->limit( 20 ),
                TextColumn::make( 'seenby' )
                ->label( 'Seen By' )
                ->badge()
                ->formatStateUsing( function ( $state, $record ) {
                    return $record->seenby === null ? 'NOT SEEN' : optional( $record->seenAdmin )->name;
                }
            )
            ->color( fn ( $state ) => $state === 'NOT SEEN' ? 'danger' : 'success' )
        ] )
        ->filters( [
            Tables\Filters\TrashedFilter::make(),
        ] )
        ->actions( [
            // Tables\Actions\EditAction::make(),
            // Tables\Actions\DeleteAction::make(),
            Tables\Actions\ForceDeleteAction::make(),
            Tables\Actions\RestoreAction::make(),
        ] )
        ->bulkActions( [
            Tables\Actions\BulkActionGroup::make( [
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ] )

        ] );
    }

    public static function getRelations(): array {
        return [
            RelationManagers\ItemsRelationManager::class,
            RelationManagers\RemarksRelationManager::class,
        ];
    }
    public static function canCreate(): bool {
        return false;
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListOrders::route( '/' ),
            'create' => Pages\CreateOrder::route( '/create' ),
            'edit' => Pages\EditOrder::route( '/{record}/edit' ),
        ];
    }
}
