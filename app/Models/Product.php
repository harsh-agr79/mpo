<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'sub_category_id',
        'price',
        'stock',
        'prod_unique_id',
        'offer',
        'hidden',
        'order_num',
        'image',
        'image_2',
        'details'
    ];

    protected $casts = [
        'order_num' => 'integer',
        'sub_category_id' => 'array',
        'offer' => 'array'
    ];

    public static function booted()
    {
        parent::booted();
        
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('order_num', 'asc');
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // public function subCategory()
    // {
    //     return $this->belongsTo(SubCategory::class, 'sub_category_id');
    // }

    public function subcategory() {
       // Check if subcategory_ids is not null and is an array
        if (!empty($this->sub_category_id) && is_array($this->sub_category_id)) {
            return Subcategory::whereIn('id', $this->sub_category_id)->get();
        }
        return collect();
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function parts()
    {
        return $this->hasMany(Part::class);
    }
}
