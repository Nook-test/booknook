<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\LibraryBook;
use App\Models\SaveBook;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaveBookController extends Controller
{
    use ApiResponder;

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function storeOrDestroy(LibraryBook $book)
    {
        if (SaveBook::where('user_id', Auth::id())->where('book_id', $book->id)->exists()) {
            SaveBook::where('user_id', Auth::id())
                ->where('book_id', $book->id)
                ->delete();
            return $this->okResponse(null, "unsaved Succefully");
        } else {
            SaveBook::create([
                'book_id' => $book->id,
                'user_id' => Auth::id()
            ]);
            return $this->okResponse(null, "saved Succefully");
        }
    }

    public function index()
    {
        $savedBooks = SaveBook::where('user_id', Auth::id())->get('book_id');
        $response = [];
        $i = 0;
        foreach($savedBooks as $book){
            $response[$i]=$book->book_id;
            $i+=1;
        }
        $books = LibraryBook::whereIn('id',$response)->get();
        return $this->okResponse(BookResource::collection($books), 'All Saved Book');
    }
}
