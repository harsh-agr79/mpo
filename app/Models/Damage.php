<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Damage extends Model
{
    protected $fillable = [
        'date',
        'user_id',
        'invoice_id',
        'cusuni_id',
        'sendbycus',
        'recbycomp',
        'sendbackbycomp',
        'recbycus',
        'mainstatus',
        'remarks',
    ];

    protected $casts = [
        'date' => 'datetime',
        'sendbycus' => 'datetime',
        'recbycomp' => 'datetime',
        'sendbackbycomp' => 'datetime',
        'recbycus' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function damageItems(): HasMany
    {
        return $this->hasMany(DamageItem::class, 'invoice_id', 'invoice_id');
    }

    // public function damageItemDetails(): HasMany
    // {
    //     return $this->hasMany(DamageItemDetail::class, 'invoice_id', 'invoice_id');
    // }
}
