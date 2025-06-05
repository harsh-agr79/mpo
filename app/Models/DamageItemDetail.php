<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DamageItemDetail extends Model
{
    protected $fillable = [
        'invoice_id',
        'damage_item_id',
        'product_id',
        'problem',
        'solution',
        'condition',
        'warranty',
        'warrantyproof',
        'replaced_part',
        'replaced_product',
        'batch_id',
    ];

     protected $casts = [
        // 'clntime' => 'integer',
        // 'date' => 'datetime',
        'replaced_part' => 'array'
    ];

    public function damage(): BelongsTo
    {
        return $this->belongsTo(Damage::class, 'invoice_id', 'invoice_id');
    }
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function damageItem(): BelongsTo
    {
        return $this->belongsTo(DamageItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function replacedPart(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'replaced_part');
    }

    public function replacedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'replaced_product');
    }
}
