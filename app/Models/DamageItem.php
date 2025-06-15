<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamageItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'quantity',
        'cusremarks',
        'adremarks',
        'instatus',
    ];

    public function damage(): BelongsTo
    {
        return $this->belongsTo(Damage::class, 'invoice_id', 'invoice_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function damageItemDetails(): HasMany
    {
        return $this->hasMany(DamageItemDetail::class);
    }
}
