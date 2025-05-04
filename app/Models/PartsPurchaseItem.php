<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartsPurchaseItem extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['invoice_id', 'part_id', 'voucher', 'quantity'];

    // Relationship with the PartsPurchase model
    public function partsPurchase()
    {
        return $this->belongsTo(PartsPurchase::class, 'invoice_id', 'invoice_id');
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
