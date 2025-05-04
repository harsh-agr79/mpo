<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductsPurchase extends BaseModel
{
    use SoftDeletes;

    protected $primaryKey = 'purchase_id';

    public $incrementing = false;

    protected $fillable = [
        'purchase_id',
        'date',
        'total_price'
    ];

    protected $keyType = 'string';
    protected $withCount = ['items'];
 
    public function items()
    {
        return $this->hasMany(ProductsPurchaseItem::class, 'purchase_id', 'purchase_id');
    }

    public function getItemsSumQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }



}
