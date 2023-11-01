<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;

    use SoftDeletes;


    protected $fillable = [
        'product_name',
        'description',
        'usd_price',
        'qar_price',
        'stock_quantity',
        'image',
    ];


    /**
     * Get all of the categories that are assigned this product.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, "category_products");
    }

    /**
     * Get the card_codes for the blog post.
     */
    public function card_codes(): HasMany
    {
        return $this->hasMany(CardCode::class);
    }

    /**
     * Get the reviews for the blog post.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // /**
    //  * Get the comments for the blog post.
    //  */
    // public function orders(): HasMany
    // {
    //     return $this->hasMany(OrderItem::class);
    // }
}
