<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class LibraryBook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'book_id',
        'quantity',
        'purchasing_price',
        'selling_price',
        'state',
        'rate',
    ];

    public function book(){
        return $this->belongsTo(Book::class);
    }
}
