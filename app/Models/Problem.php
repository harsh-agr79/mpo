<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Problem extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'problem',
        'category_id'
    ];

    protected $casts = [
        'category_id' => 'array'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
