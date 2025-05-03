<?php

namespace App\Filament\Resources\ProductsPurchaseAdjustmentResource\Pages;

use App\Filament\Resources\ProductsPurchaseAdjustmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductsPurchaseAdjustments extends ListRecords
{
    protected static string $resource = ProductsPurchaseAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Adjustment'),
        ];
    }
}
