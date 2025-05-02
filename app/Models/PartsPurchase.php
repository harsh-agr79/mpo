<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartsPurchase extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $primaryKey = 'invoice_id';

    public $incrementing = false;

    protected $fillable = ['invoice_id', 'date'];
    protected $keyType = 'string';
    protected $withCount = ['items'];


    public function items()
    {
        return $this->hasMany(PartsPurchaseItem::class, 'invoice_id', 'invoice_id');
    }
}
