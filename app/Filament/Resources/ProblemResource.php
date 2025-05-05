<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProblemResource\Pages;
use App\Filament\Resources\ProblemResource\RelationManagers;
use App\Models\Category;
use App\Models\Problem;
use DB;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProblemResource extends Resource
{
    protected static ?string $model = Problem::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';
    protected static ?string $navigationGroup = 'Faults';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('category_id')
                    ->required()
                    ->label('Categories')
                    ->multiple()
                    ->reactive()
                    ->options(Category::all()->pluck('name', 'id')),
                TextInput::make('problem')

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('problem')->sortable()->searchable(),
                TagsColumn::make('category_id')
                    ->label('Categories')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $ids = is_string($record->category_id)
                            ? json_decode($record->category_id, true)
                            : $record->category_id;

                        return Category::whereIn('id', $ids ?? [])->pluck('name')->toArray();
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make()
                    ->modalHeading(fn($record) => 'Problem: ' . ucfirst($record->id))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Section::make()
                            ->schema([

                                TextEntry::make('problem')->label('PROBLEM'),
                                TextEntry::make('category_id')->label('CATEGORIES')->state(function ($record) {
                                    return Category::whereIn('id', $record->category_id)->pluck('name')->join(', ');
                                }),
                            ])
                            ->columns(2),
                    ]),
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
            'index' => Pages\ListProblems::route('/'),
            'create' => Pages\CreateProblem::route('/create'),
            'edit' => Pages\EditProblem::route('/{record}/edit'),
        ];
    }
}
