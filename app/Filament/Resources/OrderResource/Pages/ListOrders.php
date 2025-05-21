<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'Unseen' => Tab::make()->query(fn ($query) => $query->where('seenby', NULL)),
            'pending' => Tab::make()->query(fn ($query) => $query->where('mainstatus', 'pending')),
            'approved' => Tab::make()->query(fn ($query) => $query->where('mainstatus', 'approved')->where('clnstatus', NULL)->where('delivered_at', NULL)),
            'packing' => Tab::make()->query(fn ($query) => $query->where('mainstatus', 'approved')->where('clnstatus', 'packing')),
            'rejected' => Tab::make()->query(fn ($query) => $query->where('mainstatus', 'rejected')),
            'delivered' => Tab::make()->query(fn ($query) => $query->where('mainstatus', 'delivered')->where('clnstatus', 'delivered')->whereNotNull('delivered_at')),
        ];
    }
}
