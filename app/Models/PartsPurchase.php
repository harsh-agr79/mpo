<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartsPurchase extends BaseModel
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

    public function getItemsSumQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'primary_key_value')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('table_name', 'parts_purchases')
                        ->where('primary_key_value', $this->invoice_id);
                })->orWhere(function ($q) {
                    $q->where('table_name', 'parts_purchase_items')
                        ->whereIn('primary_key_value', function ($subQuery) {
                            $subQuery->select('id')
                                ->from('parts_purchase_items')
                                ->where('invoice_id', $this->invoice_id);
                        });
                });
            });
    }

}
