<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductsPurchaseItem extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'prod_unique_id',
        'quantity'
    ];
 
    public function purchase()
    {
        return $this->belongsTo(ProductsPurchase::class, 'purchase_id', 'purchase_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'prod_unique_id', 'prod_unique_id');
    }

}
