<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\Order;

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
}
