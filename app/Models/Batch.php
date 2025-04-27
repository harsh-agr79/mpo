<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $fillable = [
        'batch_no',
        'product_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
