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
            // Actions\DeleteAction::make(),
        ];
    }

     public function getTitle(): string {
        return '';
        // Ensure nothing is rendered
    }

    public function mount( $record ): void {
        parent::mount( $record );

        $user = Auth::user();

        if (
            $user->hasPermissionTo( 'Material View First' ) &&
            is_null( $this->record->seenby )
        ) {
            $this->record->seenby = $user->id;
            $this->record->save();
        }
    }
}
