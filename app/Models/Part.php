<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Part extends BaseModel
{
    use SoftDeletes;

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
