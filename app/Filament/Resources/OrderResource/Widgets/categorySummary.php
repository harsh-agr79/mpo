<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\Order;

class categorySummary extends Widget
{
    protected static string $view = 'filament.resources.order-resource.widgets.category-summary';

    public function mount($record): void
    {
        $this->record = $record;
    }

    protected function getViewData(): array
    {
        $categoryCounts = $this->record->items()
        ->with('product.category')
        ->get()
        ->groupBy(fn ($item) => $item->product->category->name ?? 'Uncategorized')
        ->map(fn ($group) => $group->count());

        return [
            'order' => $this->record,
            'categoryCounts' => $categoryCounts,
        ];
    }

    public static function canView(): bool
    {
        return true;
    }

    protected int | string | array $columnSpan = 'full';
}
