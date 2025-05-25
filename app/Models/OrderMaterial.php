<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderMaterial extends Model
{
    protected $fillable = [
        'orderid',
        'material_id',
        'quantity',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderid');
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
