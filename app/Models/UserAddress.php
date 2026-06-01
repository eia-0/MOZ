<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'street',
        'house',
        'floor',
        'apartment',
        'entrance',
        'latitude',
        'longitude',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [];
        if ($this->street) $parts[] = 'ул. ' . $this->street;
        if ($this->house) $parts[] = 'д. ' . $this->house;
        if ($this->floor) $parts[] = 'этаж ' . $this->floor;
        if ($this->apartment) $parts[] = 'кв. ' . $this->apartment;
        if ($this->entrance) $parts[] = 'подъезд ' . $this->entrance;
        return implode(', ', $parts);
    }
}