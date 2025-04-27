<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'subcategory_id',
        'price',
        'stock',
        'prod_unique_id',
        'offer',
        'image',
        'image_2',
        'details'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategories()
    {
        return $this->belongsToMany(SubCategory::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
}
