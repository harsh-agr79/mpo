<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Role;
use App\Models\User;
use DB;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Customers';
    // protected static ?string $navigationGroup = "User Settings";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('userid')->required()->maxLength(length: 255),
                TextInput::make('name')->required()->maxLength(length: 255),
                TextInput::make('email')->email()->required()->maxLength(255),
                TextInput::make('contact')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                    ->required(fn(string $context): bool => $context === 'create')
                    ->label('Password'),
                Select::make('type')
                    ->label('Type')
                    ->options([
                        'dealer' => 'Dealer',
                        'wholesaler' => 'Wholesaler',
                        'retailer' => 'Retailer',
                        'ecommerce' => 'Ecommerce',
                    ])
                    ->required()
                    ->native(false),

                TextInput::make('shop_name')->required()->maxLength(255),
                TextInput::make('address')->required()->maxLength(255),
                TextInput::make('area')->maxLength(255),
                Select::make('state')
                    // ->relationship('subCategories', 'name')
                    ->required()
                    ->label('State/Province')
                    ->reactive()
                    ->searchable()
                    ->options(function () {
                        return DB::table('provinces')
                            ->pluck('name', 'id');
                    }),
                Select::make('district')
                    // ->relationship('subCategories', 'name')
                    ->required()
                    ->label('District')
                    ->searchable()
                    ->reactive()
                    ->options(function (Get $get) {
                        $state = $get('state'); // Get the selected category ID
                        if (!$state) {
                            return []; // If no category is selected, return an empty array
                        }

                        return DB::table('districts')
                            ->where('province_id', $state)
                            ->pluck('name', 'id');
                    }),
              
                TextInput::make('secondary_contact')->maxLength(20),
                TextInput::make('open_balance')->numeric()->default(0),
                Select::make('open_balance_type')
                    ->label('Opening Balance Type')
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Credit',
                    ])
                    ->required()
                    ->native(false), // (optional) for better UI
                TextInput::make('balance')->readonly()->numeric()->default(0),
                TextInput::make('current_balance_type')->readonly(),
                TextInput::make('ref_id')->numeric(),
                DatePicker::make('dob'),
                Select::make('tax_type')
                    ->options([
                        'VAT' => 'VAT',
                        'PAN' => 'PAN',
                    ])
                    ->required(),
                TextInput::make('tax_no')->maxLength(50),

                
                FileUpload::make('profile_image')->directory('profile_images')->image()->acceptedFileTypes(['image/jpg', 'image/svg', 'image/jpeg', 'image/png', 'image/webp'])->maxSize(2048),
                Toggle::make('disabled')->label('Disabled'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('contact')->label('Contact')->searchable(),
                TextColumn::make('role.name')->label('Role')->badge(),
                TextColumn::make('shop_name')->label('Shop Name'),
                TextColumn::make('open_balance')->label('Open Balance')->money('npr'),
                TextColumn::make('balance')->label('Balance')->money('npr'),
                TextColumn::make('disabled')
                    ->label('Disabled')
                    ->formatStateUsing(fn(bool $state) => $state ? 'true' : 'false')
                    ->badge(),

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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
