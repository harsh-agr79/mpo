<?php

namespace App\Filament\Resources\PartsPurchaseResource\Pages;

use App\Filament\Resources\PartsPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPartsPurchase extends EditRecord
{
    protected static string $resource = PartsPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
