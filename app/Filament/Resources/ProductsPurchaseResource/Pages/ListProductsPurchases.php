<?php

namespace App\Filament\Resources\ProductsPurchaseResource\Pages;

use App\Filament\Resources\ProductsPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductsPurchases extends ListRecords
{
    protected static string $resource = ProductsPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
