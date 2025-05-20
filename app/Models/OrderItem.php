<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends BaseModel
{
    protected $fillable = [
        'orderid',
        'product_id',
        'price',
        'status',
        'quantity',
        'approvedquantity',
        'offer',
    ];

    protected $casts = [
        'price' => 'integer',
        'quantity' => 'integer',
        'approvedquantity' => 'integer',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class, 'orderid', 'orderid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
