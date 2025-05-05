<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogRelationManagerResource\RelationManagers\ActivityLogsRelationManager;
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
                ViewAction::make()
                    ->modalHeading(fn($record) => 'Product: ' . ucfirst($record->prod_unique_id))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Section::make()
                            ->schema([

                                TextEntry::make('name')->label('PRODUCT NAME'),
                                TextEntry::make('prod_unique_id')->label('SLUG'),
                                ImageEntry::make('image')->label('IMAGE')->visible(fn($record) => filled($record->image)),
                                ImageEntry::make('image_2')->label('SECONDARY IMAGE')->visible(fn($record) => filled($record->image_2)),
                                TextEntry::make('category.name')->label('CATEGORY'),
                                TextEntry::make('sub_category_id')->label('SUB CATEGORIES')->state(function ($record) {
                                    return SubCategory::whereIn('id', $record->sub_category_id)->pluck('name')->join(', ');
                                })->visible(fn($record) => !is_null($record->sub_category_id)),
                                TextEntry::make('price')->label('PRICE'),
                                TextEntry::make('stock')->label('OUT OF STOCK')->state(fn($record) => $record->stock == 0 ? 'false' : 'true'),
                                TextEntry::make('hidden')->label('HIDDEN')->state(fn($record) => $record->stock == 0 ? 'false' : 'true'),
                                TextEntry::make('offer')->label('OFFER')->visible(fn($record) => filled($record->offer)),
                                TextEntry::make('details')->label('DETAILS')->visible(fn($record) => filled($record->details)),
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
            ActivityLogsRelationManager::class,
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
