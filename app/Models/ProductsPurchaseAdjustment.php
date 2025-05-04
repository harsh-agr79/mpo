<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductsPurchaseAdjustment extends BaseModel
{
    use SoftDeletes;

    protected $primaryKey = 'purchase_adj_id';

    public $incrementing = false;
    protected $fillable = [
        'purchase_adj_id',
        'date',
        'total_price'
    ];
 
    protected $keyType = 'string';
    protected $withCount = ['items'];

    public function items()
    {
        return $this->hasMany(ProductsPurchaseAdjustmentItem::class, 'purchase_adj_id', 'purchase_adj_id');
    }

    public function getItemsSumQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }
}
