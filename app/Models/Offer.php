<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'library_id', 'totalPrice', 'quantity'
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_offers', 'offer_id', 'book_id');
    }
}
