<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'currency'
    ];

    /**
     * get user who order this order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * get the payment for this order
     */

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'order_id');
    }

    /**
     * get all order items for this order
     */
    public function order_items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
