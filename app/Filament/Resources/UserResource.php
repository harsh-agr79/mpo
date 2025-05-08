<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogRelationManagerResource\RelationManagers\ActivityLogsRelationManager;
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
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

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
                    ->dehydrated(fn($state) => filled($state))
                    ->nullable()
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
                    ->height(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('dob')->label('Date of Birth (A.D.)')->date('Y-m-d')->sortable(),
                TextColumn::make('dob_nepali')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Date of Birth (B.S.)')
                    ->sortable()
                    ->getStateUsing(fn($record) => getNepaliDate($record->dob)),
                TextColumn::make('contact')->label('Contact')->searchable()->sortable(),
                TextColumn::make('type')->label('User Type')->badge()->sortable(),
                TextColumn::make('shop_name')->label(label: 'Shop Name')->sortable()->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('area')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('address')->sortable()->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('state.name')->label('Province'),
                // TextColumn::make('district.name'),
                TextColumn::make('open_balance')->label('Open Balance')->money('npr')->sortable()->toggleable(),
                TextColumn::make('balance')->label('Balance')->money('npr')->sortable()->toggleable(),
                TextColumn::make('open_balance_type')->label('Open Balance Type')->sortable()->badge()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tax_no')->label('Tax No.')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tax_type')->label('Tax Type')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('disabled')
                    ->label('Disabled')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->formatStateUsing(fn(bool $state) => $state ? 'true' : 'false')
                    ->badge(),

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->size('xl')
                    ->label('')
                    ->modalHeading(fn($record) => 'User: ' . ucfirst($record->userid))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Section::make()
                            ->schema([
                                TextEntry::make('name')->label('NAME'),
                                TextEntry::make('email')->label('EMAIL'),
                                TextEntry::make('dob')->label('DATE OF BIRTH'),
                                ImageEntry::make('profile_image')
                                    ->label('PROFILE IMAGE')
                                    ->state(fn($record) => Storage::disk('public')->url($record->profile_image))->visible(fn($record) => filled($record->profile_image)),
                                TextEntry::make('contact')->label('CONTACT'),
                                TextEntry::make('secondary_contact')->label('SECONDARY CONTACT')->visible(fn($record) => filled($record->secondary_contact)),
                                TextEntry::make(name: 'email_verified_at')->label('EMAIL VERIFIED AT')->visible(fn($record) => filled($record->email_verified_at)),
                                TextEntry::make('type')->label('TYPE'),
                                TextEntry::make(name: 'disabled')->label('DISABLED')->state(fn($record) => $record->disabled == 0 ? 'false' : 'true'),
                                TextEntry::make('shop_name')->label('SHOP NAME'),
                                TextEntry::make('address')->label('ADDRESS'),
                                TextEntry::make('area')->label('AREA'),
                                TextEntry::make('state')->label('PROVINCE'),

                                TextEntry::make('district')->label('DISTRICT'),
                                TextEntry::make('marketer_id')->label('MARKETER ID')->visible(fn($record) => filled($record->marketer_id)),
                                TextEntry::make('open_balance')->label('OPEN BALANCE')->money('npr')->visible(fn($record) => filled($record->open_balance)),
                                TextEntry::make('balance')->label('BALANCE')->money('npr')->visible(fn($record) => filled($record->balance)),
                                TextEntry::make('open_balance_type')->label('OPEN BALANCE TYPE')->visible(fn($record) => filled($record->open_balance_type)),
                                TextEntry::make('current_balance_type')->label('CURRENT BALANCE TYPE')->visible(fn($record) => filled($record->current_balance_type)),
                                TextEntry::make('tax_type')->label('TAX TYPE')->visible(fn($record) => filled($record->tax_type)),
                                TextEntry::make('tax_no')->label('TAX NO')->visible(fn($record) => filled($record->tax_no)),
                                TextEntry::make('created_at')->label('CREATED_AT'),
                                TextEntry::make('updated_at')->label('UPDATED_AT'),
                                TextEntry::make('deleted_at')->label('DELETED_AT')->visible(fn($record) => filled($record->deleted_at)),
                            ])
                            ->columns(2),
                    ]),
                Tables\Actions\EditAction::make()->size('xl')->label(''),
                Tables\Actions\DeleteAction::make()->size('xl')->label(''),
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
            ActivityLogsRelationManager::class,
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
