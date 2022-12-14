<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'area',
        'street',
        'floor',
        'near',
        'details'
    ];

    public function user(){
        $this->belongsTo('App\User');
    }
}
