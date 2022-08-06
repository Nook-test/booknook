<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuthorResource;
use App\Http\Resources\CategoryResource;
use App\Models\Author;
use App\Models\Category;
use App\Models\LibraryBook;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShortcutController extends Controller
{
    //
    public function categories($book)
    {
        $categories = Category::join('book_categories', 'categories.id', '=', 'book_categories.category_id')
            ->join('library_books', 'book_categories.book_id', '=', 'library_books.book_id')
            ->where('book_categories.book_id', $book)
            ->distinct()
            ->get('categories.*');

        return $categories;
    }

    public function authors($book)
    {
        $authors = Author::join('book_authors', 'authors.id', '=', 'book_authors.author_id')
                ->join('library_books', 'book_authors.book_id', '=', 'library_books.book_id')
                ->where('book_authors.book_id', $book)
                ->distinct()
                ->get('authors.*');

        return $authors;
    }

    public function decrease_quantity($type,$id,$quantity){
        if($type == 'book'){
            $book = LibraryBook::find($id);
            $book->update([
                $book->quantity -= (1 * $quantity)
            ]);
        } else {
            $offer = Offer::find($id);
            $offer->update([
                $offer->quantity -= (1 * $quantity)
            ]);
        }
    }

    public function increase_quantity($type,$id,$quantity){
        if($type == 'book'){
            $book = LibraryBook::find($id);
            $book->update([
                $book->quantity += (1 * $quantity)
            ]);
        } else {
            $offer = Offer::find($id);
            $offer->update([
                $offer->quantity += (1 * $quantity)
            ]);
        }
    }
}
