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

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($item) {
            $item->updateOrderTotalsAndStatus();
        });

        static::deleted(function ($item) {
            $item->updateOrderTotalsAndStatus();
        });
    }

    public function updateOrderTotalsAndStatus()
    {
        $order = $this->order;

        if ($order) {
            // Calculate total using only approved items and approvedquantity
            $total = $order->items()
                ->where('status', 'approved')
                ->sum(\DB::raw('price * approvedquantity'));

            $order->total = $total;

            if ($order->discount !== null) {
                $order->net_total = $total - ($order->discount / 100) * $total;
            } else {
                $order->net_total = $total;
            }

            // Determine mainstatus based on all item statuses
            $statuses = $order->items()->pluck('status');

            if ($statuses->contains('pending')) {
                $order->mainstatus = 'pending';
            } elseif ($statuses->every(fn($status) => $status === 'rejected')) {
                $order->mainstatus = 'rejected';
            } else {
                $order->mainstatus = 'approved';
            }

            $order->saveQuietly();
        }
    }

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
