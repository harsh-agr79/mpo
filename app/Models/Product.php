<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'sub_category_id',
        'price',
        'stock',
        'prod_unique_id',
        'offer',
        'hidden',
        'order_num',
        'image',
        'image_2',
        'details',
        'specifications',
        'images',
        'stock_count',
        'open_stock_count',
        'open_stock_date'
    ];

    protected $casts = [
        'order_num' => 'integer',
        'sub_category_id' => 'array',
        'offer' => 'array',
        'specifications' => 'array',
        'images' => 'array',
    ];

    public static function booted()
    {
        parent::booted();
        
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('order_num', 'asc');
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory() {
        if (!empty($this->sub_category_id) && is_array($this->sub_category_id)) {
            return SubCategory::whereIn('id', $this->sub_category_id)->get();
        }
        return collect();
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function parts()
    {
        return $this->hasMany(Part::class);
    }

    public function orderItems(){
        return $this->hasMany(OrderItem::class);
    }

    public function salesReturnItems(){
        return $this->hasMany(SalesReturnItem::class);
    }

    public function productsPurchaseItems()
    {
        return $this->hasMany(ProductsPurchaseItem::class, 'prod_unique_id', 'prod_unique_id');
    }

    public function productsAdjustmentItems(){
        return $this->hasMany(ProductsPurchaseAdjustmentItem::class, 'prod_unique_id', 'prod_unique_id');
    }

    public function damageItems(){
        return $this->hasMany(DamageItem::class);
    }

    public function damageItemDetails(){
        return $this->hasMany(DamageItemDetail::class);
    }

    public function damageReplacedItems()
    {
        return $this->hasMany(DamageItemDetail::class, 'replaced_product', 'id');
    }

    public function approvedQuantityAfterOpenStock()
    {
        return $this->orderItems()
            ->where('status', 'approved')
            ->whereHas('order', function ($query) {
                $query->whereDate('date', '>', $this->open_stock_date)
                    ->whereNull('deleted_at'); // exclude soft deleted orders
            })
            ->sum('approvedquantity');
    }

    public function totalSalesReturnQuantityAfterOpenStock()
    {
        return $this->salesReturnItems()
            ->whereHas('salesReturn', function ($query) {
                $query->whereDate('date', '>', $this->open_stock_date)
                    ->whereNull('deleted_at'); // exclude soft deleted sales returns
            })
            ->sum('quantity');
    }

    public function totalPurchasedQuantityAfterOpenStock()
    {
        return $this->productsPurchaseItems()
            ->whereHas('purchase', function ($query) {
                $query->whereDate('date', '>', $this->open_stock_date);
            })
            ->sum('quantity');
    }

    public function totalIncreasedQuantityAfterOpenStock()
    {
        return $this->productsAdjustmentItems()
            ->where('type', 'increase')
            ->whereHas('purchase', function ($query) {
                $query->whereDate('date', '>', $this->open_stock_date);
            })
            ->sum('quantity');
    }

    // Sum of quantity where type is 'decrease' and date > open_stock_date
    public function totalDecreasedQuantityAfterOpenStock()
    {
        return $this->productsAdjustmentItems()
            ->where('type', 'decrease')
            ->whereHas('purchase', function ($query) {
                $query->whereDate('date', '>', $this->open_stock_date);
            })
            ->sum('quantity');
    }

   public function totalDamageReplacedWithOtherAfterOpenStock()
    {
        return $this->damageReplacedItems()
            ->where('solution', 'Replaced with new other item')
            ->whereHas('damageItem.damage', function ($query) {
                $query->whereDate('date', '>', $this->open_stock_date);
                    // ->whereNull('deleted_at'); // exclude soft-deleted Damage
            })
            ->sum('quantity'); // ✅ sum instead of count
    }

    public function approvedQuantityBetween($startDate, $endDate)
    {
        return $this->orderItems()
            ->where('status', 'approved')
            ->whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->whereNull('deleted_at');
            })
            ->sum('approvedquantity');
    }

    public function approvedItemsBetween($startDate, $endDate)
    {
        return $this->orderItems()
            ->where('status', 'approved')
            ->whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->whereNull('deleted_at');
            })
            ->get();
    }

    public function totalSalesReturnQuantityBetween($startDate, $endDate)
    {
        return $this->salesReturnItems()
            ->whereHas('salesReturn', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->whereNull('deleted_at');
            })
            ->sum('quantity');
    }

    public function salesReturnItemsBetween($startDate, $endDate)
    {
        return $this->salesReturnItems()
            ->whereHas('salesReturn', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->whereNull('deleted_at');
            })
            ->get();
    }

    public function totalPurchasedQuantityBetween($startDate, $endDate)
    {
        return $this->productsPurchaseItems()
            ->whereHas('purchase', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->sum('quantity');
    }

    public function purchaseItemsBetween($startDate, $endDate)
    {
        return $this->productsPurchaseItems()
            ->whereHas('purchase', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->get();
    }

    public function totalIncreasedQuantityBetween($startDate, $endDate)
    {
        return $this->productsAdjustmentItems()
            ->where('type', 'increase')
            ->whereHas('purchase', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->sum('quantity');
    }

    public function increasedAdjustmentItemsBetween($startDate, $endDate)
    {
        return $this->productsAdjustmentItems()
            ->where('type', 'increase')
            ->whereHas('purchase', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->get();
    }

    public function totalDecreasedQuantityBetween($startDate, $endDate)
    {
        return $this->productsAdjustmentItems()
            ->where('type', 'decrease')
            ->whereHas('purchase', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->sum('quantity');
    }

    public function decreasedAdjustmentItemsBetween($startDate, $endDate)
    {
        return $this->productsAdjustmentItems()
            ->where('type', 'decrease')
            ->whereHas('purchase', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->get();
    }

    public function totalDamageReplacedWithOtherBetween($startDate, $endDate)
    {
        return $this->damageReplacedItems()
            ->where('solution', 'Replaced with new other item')
            ->whereHas('damageItem.damage', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->sum('quantity');
    }

    public function damageReplacedWithOtherItemsBetween($startDate, $endDate)
    {
        return $this->damageReplacedItems()
            ->where('solution', 'Replaced with new other item')
            ->whereHas('damageItem.damage', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->get();
    }
}
