<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaterialInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date',
        'user_id',
        'invoice_id',
        'cusuni_id',
        'mainstatus',
        'clnstatus',
        'clntime',
        'delivered_at',
        'recieved_at',
        'nepmonth',
        'nepyear',
        'othersname',
        'cartoons',
        'transport',
    ];

     protected $casts = [
        // 'save' => 'boolean',
        'clntime' => 'integer',
        // 'discount' => 'integer',
        'date' => 'datetime',
    ];

    protected $dates = ['date', 'deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(MaterialInvoiceItem::class, 'invoice_id', 'invoice_id');
    }

     public function seenAdmin()
    {
        return $this->belongsTo(Admin::class, 'seenby');
    }
}
