<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * Поля, разрешённые для массового заполнения.
     */
    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'description',
        'price',
        'weight',
        'image',
        'is_available',
    ];

    /**
     * Связь с магазином.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Связь с категорией.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}