<?php

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as permModel;

class Permission extends permModel
{
    use HasFactory;
}
