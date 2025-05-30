<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'expense_date',
        'amount',
        'particular'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
