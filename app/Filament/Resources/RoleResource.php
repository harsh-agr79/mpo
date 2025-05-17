<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\BaseModel;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use DiscoveryDesign\FilamentGaze\Forms\Components\GazeBanner;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'User Settings';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                  GazeBanner::make()
                ->pollTimer(1)
                ->lock()
                ->canTakeControl(fn() => auth()->user()?->hasRole('Admin'))
                ->hideOnCreate()
                ->columnSpanFull(),
                Forms\Components\TextInput::make('name')->unique(ignoreRecord: true),
                Forms\Components\Select::make('permissions')
                    ->multiple()
                    ->relationship(titleAttribute: 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->size('xl')->label('')->size('xl'),
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
