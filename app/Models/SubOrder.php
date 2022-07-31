<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubOrder extends Model
{
    use HasFactory;

    protected $fillable=[
        'order_id',
        'book_id',
        'offer_id',
        'quantity',
        'type'
    ];
}
