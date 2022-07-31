<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable=[
        'library_id',
        'name',
        'num_of_page',
        'searches',
        'rate',
        'pdf',
        'image',
        'summary'
    ];
}
