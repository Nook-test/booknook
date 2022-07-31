<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminInformation extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id',
        'firstName',
        'lastName',
        'middleName',
        'libraryName',
        'phone',
        'open_time',
        'close_time',
        'status',
        'image'
    ];
}
