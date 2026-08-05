<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    protected $fillable = [
        'amount',
        'effective_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_date' => 'date',
    ];
}