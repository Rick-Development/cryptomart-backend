<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class P2POrder extends Model
{
    use HasFactory;

    protected $table = 'p2p_orders';
    protected $fillable = [
        'maker_id',
        'taker_id',
        'type',
        'asset',
        'quote_currency',
        'amount',
        'price',
        'total',
        'escrow_enabled',
        'status',
        'expires_at',
        'meta'
    ];

    protected $casts = ['meta' => 'array', 'escrow_enabled' => 'boolean', 'expires_at' => 'datetime'];

    // Link to trader who created the order
    public function maker()
    {
        return $this->belongsTo(User::class, 'maker_id');
    }
}
