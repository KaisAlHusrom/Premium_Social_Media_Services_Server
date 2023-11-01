<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'item',
        'item_icon'
    ];

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
