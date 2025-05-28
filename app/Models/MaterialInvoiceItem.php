<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaterialInvoiceItem extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'material_id',
        'quantity',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($item) {
            $item->updateInvoiceStatus();
        });

        static::deleted(function ($item) {
            $item->updateInvoiceStatus();
        });
    }

    public function updateInvoiceStatus()
    {
        $invoice = $this->invoice;

        if ($invoice) {
            // Determine mainstatus based on all item statuses
            $statuses = $invoice->items()->pluck('status');

            if ($statuses->contains('pending')) {
                $invoice->mainstatus = 'pending';
            } elseif ($statuses->every(fn($status) => $status === 'rejected')) {
                $invoice->mainstatus = 'rejected';
            } else {
                $invoice->mainstatus = 'approved';
            }

            $invoice->saveQuietly();
        }
    }


    public function invoice()
    {
        return $this->belongsTo(MaterialInvoice::class, 'invoice_id', 'invoice_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
