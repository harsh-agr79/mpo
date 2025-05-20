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
use Filament\Forms\Components\ {
    TextInput, DateTimePicker, Textarea, Select, Toggle}
    ;
    use Filament\Tables\Columns\ {
        ColorColumn, TextColumn, BooleanColumn, DateTimeColumn}
        ;
        use Illuminate\Database\Eloquent\SoftDeletingScope;
        use Filament\Tables\Columns\Layout\Stack;
        use Filament\Tables\Columns\Layout\Split;

        class OrderResource extends Resource {
            protected static ?string $model = Order::class;

            protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

            public static function form( Form $form ): Form {
                return $form
                ->schema( [] );
            }

            public static function table( Table $table ): Table {
                return $table
                ->columns( [
                    TextColumn::make( 'mainstatus' )
                    ->label( '' )
                    ->formatStateUsing( function ( ?string $state ): string {
                        $colorMap = [
                            'pending'   => 'oklch(74.6% 0.16 232.661)',
                            'approved'  => 'oklch(82.8% 0.189 84.429)',
                            'packing'   => 'purple',
                            'delivered' => 'green',
                            'rejected'  => 'red',
                        ];
                        $color = $colorMap[ $state ] ?? 'gray';
                        return "<div title='{$state}' style='width: 0.5rem; height: 1.5rem; background-color: {$color};'></div>";
                    }
                )
                ->html(),
                TextColumn::make( 'date' ),

                TextColumn::make( 'user.name' ),
                TextColumn::make( 'orderid' ),

                // TextColumn::make( 'mainstatus' )->limit( 20 ),
              TextColumn::make('seenby')
    ->label('Seen By')
    ->badge()
    ->formatStateUsing(function ($state, $record) {
       return ($state === null || $state === '' || $state === 0) ? 'NOT SEEN' : optional($record->seenAdmin)->name;
    })
    ->color(fn ($state) => $state === 'NOT SEEN' ? 'danger' : 'success')
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
                //
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
