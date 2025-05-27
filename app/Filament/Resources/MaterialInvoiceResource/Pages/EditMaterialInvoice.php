<?php

namespace App\Filament\Resources\MaterialInvoiceResource\Pages;

use App\Filament\Resources\MaterialInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaterialInvoice extends EditRecord
{
    protected static string $resource = MaterialInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
