<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Http\Resources\CategoryResource;
use App\Models\BookCategory;
use App\Models\Category;
use App\Models\LibraryBook;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PharIo\Manifest\Library;

class CategoryController extends Controller
{
    //
    use ApiResponder;

    public function index()
    {
        return $this->okResponse(CategoryResource::collection(Category::all()), 'All Categories');
    }

    public function show(Category $category)
    {
        $booksId = BookCategory::where('category_id', $category->id)->get('book_id');
        $response = [];
        $j = 0;
        foreach ($booksId as $id) {
            $response[$j] = $id->book_id;
            $j += 1;
        };

        $books = LibraryBook::whereIn('id',$response)->where('deleted_at',null)->get();
        return $this->okResponse(BookResource::collection($books), 'All books in this category');
    }
}
