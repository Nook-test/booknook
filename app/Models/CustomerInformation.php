<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerInformation extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'gender',
        'phone',
        'birth_day'
    ];
}
