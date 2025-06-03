<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogRelationManagerResource\RelationManagers\ActivityLogsRelationManager;
use App\Filament\Resources\BatchResource\Pages;
use App\Filament\Resources\BatchResource\RelationManagers;
use App\Models\Batch;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use DiscoveryDesign\FilamentGaze\Forms\Components\GazeBanner;


class BatchResource extends Resource
{
    protected static ?string $model = Batch::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = "Damage";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                  GazeBanner::make()
                ->pollTimer(5)
                ->lock()
                ->canTakeControl(fn() => auth()->user()?->hasRole('Admin'))
                ->hideOnCreate()
                ->columnSpanFull(),
                Select::make('product_id')
                    ->relationship(name: 'product', titleAttribute: 'name')
                    ->searchable()
                    ->required()
                    ->options(Product::all()->pluck('name', 'id'))
                    ->label('Product'),
                TextInput::make('batch_no')
                    ->required()
                    ->unique(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch_no')->searchable()->sortable(),
                TextColumn::make('product.name')
                    ->searchable()
                    ->label('Product')
                    ->badge()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make()->size('xl')
                    ->label('')
                    ->modalHeading(fn($record) => 'Batch: ' . ucfirst($record->batch_no))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Section::make()
                            ->schema([

                                TextEntry::make('batch_no')->label('BATCH NO.'),
                                TextEntry::make('product.name')->label('PRODUCT'),
                            ])
                            ->columns(2),
                    ]),
                Tables\Actions\EditAction::make()->size('xl')->label(''),
                Tables\Actions\DeleteAction::make()->size('xl')->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ActivityLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBatches::route('/'),
            'create' => Pages\CreateBatch::route('/create'),
            'edit' => Pages\EditBatch::route('/{record}/edit'),
        ];
    }
}
