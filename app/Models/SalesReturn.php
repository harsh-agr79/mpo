<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesReturn extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date',
        'user_id',
        'return_id',
        'cusuni_id',
        'discount',
        'total',
        'net_total',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

     protected static function boot()
    {
        parent::boot();

        static::creating(function ($salesReturn) {
            self::applyDiscountAndNepaliDate($salesReturn);
        });

        static::updating(function ($salesReturn) {
            self::applyDiscountAndNepaliDate($salesReturn, true);
        });
    }

    protected static function applyDiscountAndNepaliDate($salesReturn, $isUpdate = false)
    {
        // Handle discount & net total
        $dis = $salesReturn->discount ?? 0;
        if ($salesReturn->total) {
            $salesReturn->net_total = $salesReturn->total - ($dis / 100) * $salesReturn->total;
        }

        // Handle Nepali date conversion (if creating or if date changed)
        if (!$isUpdate || $salesReturn->isDirty('date')) {
            $salesReturn->nepmonth = getNepaliMonth($salesReturn->date);
            $salesReturn->nepyear = getNepaliYear($salesReturn->date);
        }
    }

    /**
     * A sales return belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A sales return has many sales return items.
     */
    public function items()
    {
        return $this->hasMany(SalesReturnItem::class, 'return_id', 'return_id');
    }
}
