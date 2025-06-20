<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Traits\HasActivityLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasActivityLogs;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'userid',
        'contact',
        'type',
        'disabled',
        'shop_name',
        'address',
        'area',
        'state',
        'district',
        'marketer_id',
        'open_balance',
        'balance',
        'profile_image',
        'secondary_contact',
        'dob',
        'tax_type',
        'tax_no',
        'open_balance_type',
        'current_balance_type',
        'thirdays',
        'fourdays',
        'sixdays',
        'nindays',
        'activity',
        'bill_count',
        'invoice_permission',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses() {
        return $this->hasMany(Expense::class);
    }

    public function targets()
    {
        return $this->hasMany(Target::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function materialInvoices()
    {
        return $this->hasMany(MaterialInvoice::class);
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class);
    }
}
