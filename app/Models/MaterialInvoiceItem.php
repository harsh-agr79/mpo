<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaterialInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'material_id',
        'quantity',
        'status',
    ];

    public function invoice()
    {
        return $this->belongsTo(MaterialInvoice::class, 'invoice_id', 'invoice_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
