<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\Order;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\TextEntry;

class OrderSummary extends Widget
{
    protected static string $view = 'filament.resources.order-resource.widgets.order-summary';

    public $record;

    public function mount($record): void
    {
        $this->record = $record;
    }

    protected function getViewData(): array
    {
        return [
            'order' => $this->record,
        ];
    }

    public static function canView(): bool
    {
        return true;
    }

    protected int | string | array $columnSpan = 'full';

    public function getInfolist(): Infolist
    {
        return Infolist::make()
            ->record($this->record)  // <-- important
            ->columns(2)
            ->schema([
                // Always visible fields
                TextEntry::make('user.name')->label('Name'),
                TextEntry::make('date')->label('Date'),

                // Collapsible group (Alpine.js toggle in Blade)
                Group::make([
                    TextEntry::make('user.shop_name')->label('Shop'),
                    TextEntry::make('user.contact')->label('Phone no.'),
                    TextEntry::make('user.address')->label('Address'),
                    TextEntry::make('user.tax_no')->label('Pan no.'),
                    TextEntry::make('orderid')->label('Order ID'),
                    TextEntry::make('miti')
                        ->label('Miti')
                        ->default(fn () => getNepaliDate($this->record->date)),
                ])
                ->extraAttributes(['x-show' => 'open'])
                ->columnSpanFull(),
            ]);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view(static::$view, [
            'infoList' => $this->getInfolist(),
        ]);
    }
}
