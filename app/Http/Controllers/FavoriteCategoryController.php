<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\FavoriteCategory;
use App\Models\LibraryBook;
use App\Models\User;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FavoriteCategoryController extends Controller
{
    //
    use ApiResponder;

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'favorites' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }

        $user = User::findOrFail(Auth::id());
        $user->categories()->sync($request['favorites']);

        return $this->okResponse(null, 'all your favorate categories added successfully');
    }

    public function showFavoriteCategories()
    {
        $categories = Category::join('favorite_categories', 'categories.id', '=', 'favorite_categories.category_id')
            ->where('favorite_categories.user_id', Auth::id())
            ->get('categories.*');
        return $this->okResponse(
            CategoryResource::collection($categories),
            'your favorite category'
        );
    }


    public function showBooksInFavoriteCategories()
    {
        $books_in_user_favorite_categories = FavoriteCategory::join('book_categories', 'favorite_categories.category_id', 'book_categories.category_id')
            ->join('library_books', 'library_books.book_id', '=', 'book_categories.book_id')
            ->where('favorite_categories.user_id', Auth::id())
            ->get('library_books.id');

        $response = [];
        $j = 0;
        foreach ($books_in_user_favorite_categories as $id) {
            $response[$j] = $id->id;
            $j += 1;
        };

        $books = LibraryBook::whereIn('id',$response)->where('deleted_at',null)->get();
        return $this->okResponse(BookResource::collection($books), 'books in your favorite categories');
    }
}
