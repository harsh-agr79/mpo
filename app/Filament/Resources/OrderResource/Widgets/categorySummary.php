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
       $items = $this->record->items()->with('product.category')->get();

        $grouped = $items->groupBy(fn ($item) => $item->product->category->name ?? 'Uncategorized');

        $categoryCounts = $grouped->map(fn ($group) => $group->count());

        $categoryApprovedSums = $grouped->map(fn ($group) => $group->sum('approvedquantity'));

        $categoryApprovedValueSums = $grouped->map(fn ($group) =>
            $group->sum(fn ($item) => ($item->approvedquantity ?? 0) * ($item->price ?? 0))
        );

        // Totals
        $totalItems = $items->count();
        $totalApprovedQuantity = $items->sum('approvedquantity');
        $totalApprovedValue = $items->sum(fn ($item) => ($item->approvedquantity ?? 0) * ($item->price ?? 0));

        $discount = $this->record->discount ?? 0;
        $finalTotal = $totalApprovedValue - (($discount/100) * $totalApprovedValue);

         $totalBenefit = $items->sum(function ($item) {
            $actualPrice = $item->actualprice ?? 0;
            $price = $item->price ?? 0;
            $approvedQty = $item->approvedquantity ?? 0;
            return ($actualPrice > $price) ? ($actualPrice - $price) * $approvedQty : 0;
        });

        return [
            'order' => $this->record,
            'categoryCounts' => $categoryCounts,
            'categoryApprovedSums' => $categoryApprovedSums,
            'categoryApprovedValueSums' => $categoryApprovedValueSums,
            'totalItems' => $totalItems,
            'totalApprovedQuantity' => $totalApprovedQuantity,
            'totalApprovedValue' => $totalApprovedValue,
            'discount' => $discount,
            'finalTotal' => $finalTotal,
            'totalBenefit' => $totalBenefit,
        ];
    }

    public static function canView(): bool
    {
        return true;
    }

    protected int | string | array $columnSpan = 'full';
}
