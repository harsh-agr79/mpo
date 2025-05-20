<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class order_remarks extends Model
{
   protected $fillable = [
        'orderid',
        'remark',
        'remarks_by',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class, 'orderid', 'orderid');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'remarks_by');
    }
}
