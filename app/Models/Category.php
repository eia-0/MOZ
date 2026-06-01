<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'parent_id'];

    // Подкатегории
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Родительская категория
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Только корневые категории (без родителя)
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // Товары этой категории
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}