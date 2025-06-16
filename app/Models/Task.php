<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'order_column',
        'assigned_to',
        'assigned_by',
        'due_date',
        'priority',
    ];

    protected $casts = [
        // 'tags' => 'array',
        'due_date' => 'date',
        // 'start_date' => 'datetime',
        // 'completed_at' => 'datetime',
        // 'is_archived' => 'boolean',
        'assigned_to' => 'array',
    ];

    // protected $appends = ['assigned_user_names'];
    protected $appends = ['assigned_admin_names'];

    // Relationships
    // public function assignedTo()
    // {
    //     return $this->belongsTo(Admin::class, 'assigned_to');
    // }
    public function assignedUsers()
    {
        return Admin::whereIn('id', $this->assigned_to ?? [])->get();
    }

    public function assignedUserNames(): Attribute
    {
        return Attribute::get(fn () => 
            Admin::whereIn('id', $this->assigned_to ?? [])
                ->pluck('name')
                ->toArray()
        );
    }

    public function assignedAdmins()
    {
        // Safely cast to array if null
        $assignedIds = is_array($this->assigned_to) ? $this->assigned_to : [];

        return Admin::whereIn('id', $assignedIds)
            ->get();
    }

    protected function assignedAdminNames(): Attribute
    {
        return Attribute::get(function () {
            $ids = is_array($this->assigned_to) ? $this->assigned_to : [];

            return Admin::whereIn('id', $ids)
                // ->where('role', 'admin')
                ->pluck('name')
                ->implode(', ');
        });
    }

    public function assignedBy()
    {
        return $this->belongsTo(Admin::class, 'assigned_by');
    }
}
