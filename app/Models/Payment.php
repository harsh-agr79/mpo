<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'payment_date',
        'amount',
        'voucher',
        'remarks'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
