<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_name'
    ];

    /**
     * Get the items for the content.
     */
    public function content_items()
    {
        return $this->hasMany(ContentItem::class);
    }
}
