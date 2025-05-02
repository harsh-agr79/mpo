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


    protected static function booted()
    {
        static::creating(function ($model) {
            $model->invoice_id = getNepaliInvoiceId(); // from your helper
        });
    }


    public function items()
    {
        return $this->hasMany(PartsPurchaseItem::class, 'invoice_id', 'invoice_id');
    }
}
