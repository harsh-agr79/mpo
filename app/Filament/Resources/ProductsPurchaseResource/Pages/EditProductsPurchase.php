<?php

namespace App\Filament\Resources\ProductsPurchaseResource\Pages;

use App\Filament\Resources\ProductsPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductsPurchase extends EditRecord
{
    protected static string $resource = ProductsPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
