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

        static::creating(function ($order) {
            self::applyDiscountAndNepaliDate($order);
        });

        static::updating(function ($order) {
            self::applyDiscountAndNepaliDate($order, true);
        });
    }

    protected static function applyDiscountAndNepaliDate($order, $isUpdate = false)
    {
        // Handle discount & net total
        $dis = $order->discount ?? 0;
        if ($order->total) {
            $order->net_total = $order->total - ($dis / 100) * $order->total;
        }

        // Handle Nepali date conversion (if creating or if date changed)
        if (!$isUpdate || $order->isDirty('date')) {
            $order->nepmonth = getNepaliMonth($order->date);
            $order->nepyear = getNepaliYear($order->date);
        }
    }

    public function getComputedNetTotalAttribute()
    {
        if ($this->net_total > 0) {
            return $this->net_total;
        }

        return $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
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
        return $this->hasMany(order_remarks::class, 'orderid', 'orderid');
    }

    public function getItemsTotalAttribute()
    {
        return $this->approvedquantity*$this->price;
    }

    public function materials()
    {
        return $this->hasMany(OrderMaterial::class, 'orderid', 'orderid');
    }

}
