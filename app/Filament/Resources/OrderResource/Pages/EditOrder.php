<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\OrderResource\Widgets\OrderSummary;
use App\Filament\Resources\OrderResource\Widgets\categorySummary;
use App\Models\Order;
use App\Filament\Resources\OrderResource\RelationManagers;


class EditOrder extends EditRecord {
    protected static string $resource = OrderResource::class;

    public function getTitle(): string {
        return '';
        // Ensure nothing is rendered
    }
    protected function getHeaderWidgets(): array {
        return [
            OrderSummary::class,
        ];
    }

    protected function getFooterWidgets(): array {
        return [
            categorySummary::class,
        ];
    }

    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }
    

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
            // Actions\DeleteAction::make(),
        ];
    }
}
