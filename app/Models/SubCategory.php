<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends BaseModel
{
    protected $fillable = ['name', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->hasMany(SubCategory::class);
    }
}
