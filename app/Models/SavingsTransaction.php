<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsTransaction extends Model
{
    use HasFactory;

    protected $table = 'savings_transactions';

    protected $fillable = [
        'user_id',
        'savings_id',
        'amount',
        'type',
        'status',
        'source',
        'narration'
    ];

    // Relationship with user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with user
    public function savings()
    {
        return $this->belongsTo(Savings::class);
    }
}