<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantApplication extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'phone',
        'whatsapp',
        'business_name',
        'quidax_usdt_balance',
        'min_usdt_required',
        'balance_verified_at',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'quidax_usdt_balance' => 'decimal:8',
        'min_usdt_required' => 'decimal:8',
        'balance_verified_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(\App\Models\Admin\Admin::class, 'reviewed_by');
    }
}
