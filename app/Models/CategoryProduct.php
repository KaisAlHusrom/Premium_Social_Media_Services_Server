<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'description',
        'is_service',
        'category_icon',
        'category_banner_image',
        'category_image'
    ];

    /**
     * Get all of the Products for the Category.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, "category_products");
    }
}
