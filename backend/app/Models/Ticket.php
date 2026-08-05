<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
    'control_no',
    'date',
    'plate_number',
    'driver',
    'company',
    'amount'
];
}
