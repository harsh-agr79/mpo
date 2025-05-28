<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesReturnItem extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'product_id',
        'price',
        'quantity',
    ];

    /**
     * A sales return item belongs to a sales return.
     */
    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class, 'return_id', 'return_id');
    }

    /**
     * A sales return item belongs to a product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->withDefault();
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($item) {
            self::recalculateSalesReturn($item->return_id);
        });

        static::updated(function ($item) {
            self::recalculateSalesReturn($item->return_id);
        });

        static::deleted(function ($item) {
            self::recalculateSalesReturn($item->return_id);
        });
    }

    protected static function recalculateSalesReturn($returnId)
    {
        $salesReturn = SalesReturn::where('return_id', $returnId)->first();

        if (!$salesReturn) return;

        $total = $salesReturn->items()->sum(\DB::raw('price * quantity'));
        $salesReturn->total = $total;

        $discount = $salesReturn->discount ?? 0;
        $salesReturn->net_total = $total - ($discount / 100) * $total;

        $salesReturn->saveQuietly(); // Avoid triggering infinite loop
    }
}
