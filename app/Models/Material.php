<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Material extends BaseModel
{
    use SoftDeletes;

    protected $fillable = ['name', 'image', 'out_of_stock'];
}
