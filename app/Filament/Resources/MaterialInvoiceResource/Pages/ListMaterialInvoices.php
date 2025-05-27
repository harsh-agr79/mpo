<?php

namespace App\Filament\Resources\MaterialInvoiceResource\Pages;

use App\Filament\Resources\MaterialInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaterialInvoices extends ListRecords
{
    protected static string $resource = MaterialInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
