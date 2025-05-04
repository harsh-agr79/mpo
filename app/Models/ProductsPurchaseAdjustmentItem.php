<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductsPurchaseAdjustmentItem extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_adj_id',
        'prod_unique_id',
        'type',
        'quantity'
    ]; 

    public function purchase()
    {
        return $this->belongsTo(ProductsPurchaseAdjustment::class, 'purchase_adj_id', 'purchase_adj_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'prod_unique_id', 'prod_unique_id');
    }
}
 