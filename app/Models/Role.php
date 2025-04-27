<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as roleModel;

class Role extends roleModel
{
    use HasFactory;
}
