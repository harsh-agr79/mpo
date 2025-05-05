<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends BaseModel
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'order_num'
    ];

    protected $casts = [
        'order_num' => 'integer',
    ];

    public static function booted()
    {
        parent::booted();
        
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('order_num', 'asc');
        });
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function problems()
    {
        return $this->hasMany(Problem::class);
    }
}
