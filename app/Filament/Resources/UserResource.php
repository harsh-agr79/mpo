<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Filament\Resources\UserResource\RelationManagers\TargetsRelationManager;
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
use Filament\Tables\Columns\ImageColumn;
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
                TextInput::make('userid')->required()->maxLength(length: 255)->unique(ignoreRecord: true),
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
                ImageColumn::make('profile_image')
                    ->label('Avatar')
                    ->square()
                    ->width(100)
                    ->height(100),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('dob')->label('Date of Birth (A.D.)')->date('Y-m-d')->sortable(),
                TextColumn::make('dob_nepali')
                    ->label('Date of Birth (B.S.)')
                    ->sortable()
                    ->getStateUsing(fn($record) => getNepaliDate($record->dob)),
                TextColumn::make('contact')->label('Contact')->searchable()->sortable(),
                TextColumn::make('type')->label('User Type')->badge()->sortable(),
                TextColumn::make('shop_name')->label('Shop Name')->sortable(),
                TextColumn::make('area')->sortable(),
                TextColumn::make('address')->sortable(),
                // TextColumn::make('state.name')->label('Province'),
                // TextColumn::make('district.name'),
                TextColumn::make('open_balance')->label('Open Balance')->money('npr')->sortable(),
                TextColumn::make('balance')->label('Balance')->money('npr')->sortable(),
                TextColumn::make('open_balance_type')->label('Open Balance Type')->sortable(),
                TextColumn::make('tax_no')->label('Tax No.')->sortable(),
                TextColumn::make('tax_type')->label('Tax Type')->sortable(),
                TextColumn::make('disabled')
                    ->label('Disabled')
                    ->sortable()
                    ->formatStateUsing(fn(bool $state) => $state ? 'true' : 'false')
                    ->badge(),

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
            TargetsRelationManager::class,
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
