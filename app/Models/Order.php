<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'date',
        'user_id',
        'orderid',
        'cusuni_id',
        'mainstatus',
        'discount',
        'save',
        'clnstatus',
        'clntime',
        'seenby',
        'total',
        'net_total',
        'user_remarks',
        'delivered_at',
        'recieved_at',
        'nepmonth',
        'nepyear',
        'othersname',
        'cartoons',
        'transport',
    ];

    protected $casts = [
        'save' => 'boolean',
        'clntime' => 'integer',
        'discount' => 'integer',
        'date' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seenAdmin()
    {
        return $this->belongsTo(Admin::class, 'seenby');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'orderid', 'orderid');
    }

    public function remarks()
    {
        return $this->hasMany(OrderRemark::class, 'orderid', 'orderid');
    }
}
