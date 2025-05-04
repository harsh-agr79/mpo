<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Resource extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'path',
        'type'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Ensure type is set based on the uploaded file
            if ($model->path) {
                // If path is set, get the file extension
                $extension = File::extension(Storage::path($model->path));
                $model->type = $extension; // Set type field with extension
            }
        });
    }
}
