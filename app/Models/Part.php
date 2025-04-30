<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $fillable = [
        'name',
        'product_id',
        'open_balance',
        'image'
    ];

    protected $casts = [
        'product_id' => 'array'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
