<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminInformation extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'library_name',
        'phone',
        'open_time',
        'close_time',
        'status',
        'image'
    ];

    public function user(){
        return $this->belongsTo('App\User');
    }
}
