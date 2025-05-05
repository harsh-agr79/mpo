<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use Filament\Actions;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    public function mount(int|string $record): void
    {
        // Load the record by its ID
        $this->record = ActivityLog::findOrFail($record);

        // Optionally, you can also load any additional data here
        parent::mount($record);
    }

    
}
