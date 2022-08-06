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
        'pdf',
        'image',
        'summary'
    ];

    public function categories(){
        return $this->belongsToMany(Category::class, 'book_categories', 'book_id', 'category_id');
    }

    public function authors(){
        return $this->belongsToMany(Author::class, 'book_authors', 'book_id', 'author_id');
    }

    public function offers(){
        return $this->belongsToMany(Offer::class,'book_offers','offer_id','book_id');
    }

    public function library_book(){
        return $this->hasMany(LibraryBook::class);
    }

}
