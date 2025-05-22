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
        // 'offer' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

         $handleOfferPricing = function ($item) {
            if ($item->status === 'approved') {
                $offer = is_string($item->offer) ? json_decode($item->offer, true) : $item->offer;

                // Offer is filled: apply first value as price
                if (is_array($offer) && !empty($offer)) {
                    $item->price = (int) reset($offer);
                }

                // Offer is now null/empty — but was previously filled (only for updating)
                elseif ($item->exists) {
                    $originalOffer = is_string($item->getOriginal('offer'))
                        ? json_decode($item->getOriginal('offer'), true)
                        : $item->getOriginal('offer');

                    if (is_array($originalOffer) && !empty($originalOffer)) {
                        // Offer changed from filled → empty/null
                        $item->price = optional($item->product)->price ?? $item->price;
                    }
                }
            }
        };

        static::creating($handleOfferPricing);
        static::updating($handleOfferPricing);

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
