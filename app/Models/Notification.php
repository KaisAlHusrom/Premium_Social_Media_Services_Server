<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'is_read'
    ];

    /**
     * get user who order this order
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
