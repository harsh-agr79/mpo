<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\SubCategory;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required()->label('Product Name'),
                Select::make('category_id')
                    ->live()
                    ->relationship('category', 'name')
                    ->required()
                    ->label('Category'),
                Select::make('sub_category_id')
                    ->relationship('subCategory', 'name')
                    ->required()
                    ->label('SubCategory')
                    ->options(function (Get $get) {
                        $categoryId = $get('category_id');

                        if ($categoryId) {
                            return SubCategory::where('category_id', $categoryId)->pluck('name', 'id')->toArray();
                        }
                    }),
                TextInput::make('price')->required()
                    ->label('Price')
                    ->numeric()
                    ->inputMode('decimal'),
                TextInput::make('stock')->required()
                    ->label('Stock')
                    ->numeric()
                    ->inputMode('integer'),
                TextInput::make('prod_unique_id')->unique()->required()->label('Unique ID/Slug'),
                TextInput::make('offer')
                    ->numeric()
                    ->inputMode('decimal')
                    ->label('Offer'),
                TextInput::make('order_num')
                    ->numeric()
                    ->inputMode('integer')
                    ->label('Order'),
                FileUpload::make('image')
                    ->required()
                    ->directory('products')
                    ->image()
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/svg', 'image/png', 'image/jpg', 'image/jpeg', 'image/webp'])
                    ->label('Image'),
                FileUpload::make('image_2')
                    ->directory('products')
                    ->image()
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/svg', 'image/png', 'image/jpg', 'image/jpeg', 'image/webp'])
                    ->label('Additional Image'),
                Textarea::make('details')
                    ->label('Product Details')
                    ->columnSpanFull()
                    ->rows(10),
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
                    ->height(100),
                TextColumn::make('name'),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge(),
                TextColumn::make('subCategory.name')
                    ->label('SubCategory')
                    ->badge(),
                TextColumn::make('price')->label('Price'),
                TextColumn::make('stock')->label('Stock'),
                TextColumn::make('prod_unique_id')->label('Slug'),
                TextColumn::make('offer')->label('Offer'),
                ImageColumn::make('image_2')->label('Additional Image')->square()
                    ->width(100)
                    ->height(100),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
