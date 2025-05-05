<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogRelationManagerResource\RelationManagers\ActivityLogsRelationManager;
use App\Filament\Resources\ResourceResource\Pages;
use App\Filament\Resources\ResourceResource\RelationManagers;
use App\Models\Resource as ResourceModel;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\File;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Str;

class ResourceResource extends Resource
{
    protected static ?string $model = ResourceModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->label('Resource Name'),
                FileUpload::make('path')
                    ->required()
                    ->label('File')
                    ->directory('resources')
                    ->preserveFilenames()
                    ->afterStateUpdated(function ($state, $set) {
                        // Check if a file was uploaded
                        if ($state) {
                            // Get the file instance from the state (this is the actual file object)
                            $file = $state->getClientOriginalName(); // Get the original file name
            
                            // Get the file extension using the original file name
                            $extension = File::extension($file);

                            // Set the 'type' field with the correct file extension
                            $set('type', $extension);
                        }
                    }),
                TextInput::make('type')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')->searchable()->sortable(),
                TextColumn::make('type')->label('Type')->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn($record) => 'Resource: ' . ucfirst($record->id))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Section::make()
                            ->schema([

                                TextEntry::make('name')->label('NAME'),
                                TextEntry::make('type')->label('FILE TYPE'),
                                // Display image if the file is an image
                                ImageEntry::make('path')
                                    ->label('File Preview')
                                    ->visible(fn($record) => in_array(strtolower($record->type), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                    ->state(fn($record) => Storage::disk('public')->url($record->path)),

                                // Show download link for non-image files
                                TextEntry::make('download_link')
                                    ->label('Download File')
                                    ->state(fn($record) => Storage::disk('public')->url($record->path))
                                    ->visible(fn($record) => !in_array(strtolower($record->type), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                    ->formatStateUsing(fn($state) => "<a href='{$state}' target='_blank' class='text-primary underline'>Download</a>")
                                    ->html(),
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
            ActivityLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResources::route('/'),
            'create' => Pages\CreateResource::route('/create'),
            'edit' => Pages\EditResource::route('/{record}/edit'),
        ];
    }

    public static function save(Form $form, $record)
    {
        // Manually set the 'type' before saving
        $state = $form->getState();
        if (isset($state['path']) && $state['path']) {
            // Ensure type is set based on the file uploaded
            $extension = File::extension(Storage::path($state['path']));
            $state['type'] = $extension;
        }

        // Save the record with the updated 'type' field
        $record->fill($state); // Fill the model with the form data
        $record->save(); // Save the record
    }

}
