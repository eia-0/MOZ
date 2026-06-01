<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',        // <-- новое поле
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function store(): HasOne
    {
        return $this->hasOne(Store::class);
    }

    public function isStore(): bool
    {
        return $this->role === 'store';
    }

    public function isCourier(): bool
    {
        return $this->role === 'courier';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }
}