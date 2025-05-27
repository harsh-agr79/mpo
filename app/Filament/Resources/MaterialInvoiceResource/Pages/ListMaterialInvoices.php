<?php

namespace App\Filament\Resources\MaterialInvoiceResource\Pages;

use App\Filament\Resources\MaterialInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;

class ListMaterialInvoices extends ListRecords
{
    protected static string $resource = MaterialInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

     public function getTitle(): string
            {
                return ''; // Ensure nothing is rendered
            }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'Unseen' => Tab::make()->query(fn ($query) => $query->where('seenby', NULL)),
            'pending' => Tab::make()->query(fn ($query) => $query->where('mainstatus', 'pending')->whereNotNull('seenby')),
            'approved' => Tab::make()->query(fn ($query) => $query->where('mainstatus', 'approved')->where('clnstatus', NULL)->where('delivered_at', NULL)),
            'packing' => Tab::make()->query(fn ($query) => $query->where('mainstatus', 'approved')->where('clnstatus', 'packing')),
            'rejected' => Tab::make()->query(fn ($query) => $query->where('mainstatus', 'rejected')),
            'delivered' => Tab::make()->query(fn ($query) => $query->where('mainstatus', 'approved')->where('clnstatus', 'delivered')->whereNotNull('delivered_at')),
        ];
    }
}
