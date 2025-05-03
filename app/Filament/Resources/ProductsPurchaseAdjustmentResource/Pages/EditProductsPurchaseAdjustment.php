<?php

namespace App\Filament\Resources\ProductsPurchaseAdjustmentResource\Pages;

use App\Filament\Resources\ProductsPurchaseAdjustmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductsPurchaseAdjustment extends EditRecord
{
    protected static string $resource = ProductsPurchaseAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
