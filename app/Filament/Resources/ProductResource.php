<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = "Inventory";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required()->label('Product Name'),
                Select::make('category_id')
                    ->live()
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->options(Category::all()->pluck('name', 'id'))
                    ->label('Category'),
                Select::make('sub_category_id')
                    ->required()
                    ->label('SubCategories')
                    ->multiple()
                    ->reactive()
                    ->options(function (Get $get) {
                        $categoryId = $get('category_id'); // Get the selected category ID
                        if (!$categoryId) {
                            return []; // If no category is selected, return an empty array
                        }

                        // Fetch subcategories dynamically based on the selected category
                        return DB::table('sub_categories')
                            ->where('category_id', $categoryId)
                            ->pluck('name', 'id');
                    }),
                TextInput::make('price')->required()
                    ->label('Price')
                    ->numeric()
                    ->inputMode('decimal'),
                Toggle::make('stock')
                    ->label('Out of Stock'),
                Toggle::make('hidden')
                    ->label('Hide'),
                TextInput::make('prod_unique_id')->unique(ignoreRecord: true)->required()->label('Unique ID/Slug')
                ,
                TextInput::make('offer')
                    ->numeric()
                    ->inputMode('decimal')
                    ->label('Offer'),
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
            ->reorderable('order_num')
            ->defaultSort('order_num')
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->square()
                    ->width(100)
                    ->height(100),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->sortable(),
                // TextColumn::make('subCategory.name')
                //     ->label('SubCategory')
                //     ->badge(),
                TextColumn::make('price')->label('Price')->money('npr')->sortable(),
                TextColumn::make('stock')->label('Stock')->sortable(),
                TextColumn::make('prod_unique_id')->label('Slug')->searchable()->sortable(),
                TextColumn::make('offer')->label('Offer')->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
