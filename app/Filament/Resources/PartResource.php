<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartResource\Pages;
use App\Filament\Resources\PartResource\RelationManagers;
use App\Models\Part;
use App\Models\Product;
use DB;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PartResource extends Resource
{
    protected static ?string $model = Part::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Products')
                    ->multiple()
                    ->reactive()
                    ->options(Product::all()->pluck('name', 'id')),
                TextInput::make('name')
                    ->required()
                    ->label('Parts Name'),
                TextInput::make('open_balance')
                    ->numeric()
                    ->inputMode('integer')
                    ->nullable(),
                FileUpload::make('image')
                    ->directory('parts')
                    ->image()
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/svg', 'image/png', 'image/jpg', 'image/jpeg', 'image/webp'])
                    ->label('Image'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->square()
                    ->width(100)
                    ->height(100)
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('open_balance')
                    ->sortable()
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make()
                    ->modalHeading(fn($record) => 'Part: ' . ucfirst($record->id))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Section::make()
                            ->schema([

                                TextEntry::make('name')->label('PART NAME'),
                                ImageEntry::make('image')->label('IMAGE')->visible(fn($record) => filled($record->image)),
                                TextEntry::make('product.name')->label('PRODUCT')->visible(fn($record) => filled($record->product)),
                                TextEntry::make('open_balance')->label('OPEN BALANCE')->visible(fn($record) => filled($record->open_balance)),
                                TextEntry::make('created_at')->label('CREATED_AT'),
                                TextEntry::make('updated_at')->label('UPDATED_AT'),
                                TextEntry::make('deleted_at')->label('DELETED_AT')->visible(fn($record) => filled($record->deleted_at)),

                            ])
                            ->columns(2),
                    ]),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParts::route('/'),
            'create' => Pages\CreatePart::route('/create'),
            'edit' => Pages\EditPart::route('/{record}/edit'),
        ];
    }
}
