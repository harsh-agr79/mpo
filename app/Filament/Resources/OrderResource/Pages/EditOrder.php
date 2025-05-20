<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditOrder extends EditRecord {
    protected static string $resource = OrderResource::class;

    public function mount( $record ): void {
        parent::mount( $record );

        $user = Auth::user();

        if (
            $user->hasPermissionTo( 'Order View First' ) &&
            is_null( $this->record->seenby )
        ) {
            $this->record->seenby = $user->id;
            $this->record->save();
        }
    }

    protected function getHeaderActions(): array {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
