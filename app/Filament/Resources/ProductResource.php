<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // TextInput::make('name')->required()->label('Product Name'),
                // Select::make('category_id')
                //     ->relationship('category', 'name')
                //     ->required()
                //     ->label('Category'),
                // Select::make('subcategory_id')
                //     ->relationship('subcategory', 'name')
                //     ->required()
                //     ->label('SubCategory'),
                // TextInput::make('price')->required()
                //     ->label('Price')
                //     ->type('number')
                //     ->min(0),
                // TextInput::make('prod_unique_id')->unique()->required()->label('Product Unique ID'),
                // TextInput::make('offer')->nullable()->label('Offer'),
                // TextInput::make('image')->nullable()->label('Image'),
                // TextInput::make('image_2')->nullable()->label('Additional Image'),
                // TextInput::make('details')->nullable()->label('Product Details'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('name')->label('Product Name'),
                // TextColumn::make('category.name')->label('Category'),
                // TextColumn::make('subcategory.name')->label('SubCategory'),
                // TextColumn::make('price')->label('Price'),
                // ImageColumn::make('image')->label('Image'),
                // TextColumn::make('prod_unique_id')->label('Product Unique ID'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
