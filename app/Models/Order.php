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

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($order) {
            $dis = 0;
            if ($order->discount !== null) {
                $dis = $order->discount;
            }
            if ($order->total) {
                $order->net_total = $order->total - ($dis / 100) * $order->total;
            }
        });

        // Optional: Handle create also
        static::creating(function ($order) {
            $dis = 0;
            if ($order->discount !== null) {
                $dis = $order->discount;
            }
            if ($order->total) {
                $order->net_total = $order->total - ($dis / 100) * $order->total;
            }
        });
    }

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

    public function getItemsTotalAttribute()
    {
        return $this->approvedquantity*$this->price;
    }
}
