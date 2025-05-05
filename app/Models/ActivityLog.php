<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_name',
        'primary_key_value',
        'operation',
        'old_data',
        'new_data',
        'admin_id',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];


    /**
     * Optional: Relationship to User who performed the action
     */
    public function user()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Optional: Human-readable time
     */
    public function getTimeAgoAttribute(): string
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }
}
