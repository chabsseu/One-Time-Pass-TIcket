<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
    'control_no',
    'date',
    'plate_number',
    'driver',
    'company',
    'amount'
];
}
