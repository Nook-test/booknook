<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\LibraryBook;
use App\Models\Rate;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RateController extends Controller
{
    use ApiResponder;

    public function __construct()
    {
        $this->middleware('auth:api')->except('top_rated');
    }

    public function storeOrUpdate(Request $request, LibraryBook $book)
    {
        $validator = Validator::make($request->all(), [
            'star' => 'required|numeric|between:1,5'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }


        if (Rate::where('book_id', $book->id)->where('user_id', Auth::id())->exists()) {
            $rate = Rate::where('book_id', $book->id)->where('user_id', Auth::id())->first();
            $rate->update([
                $rate->star = $request->star
            ]);
        } else {
            $rate = new Rate();
            $rate->user_id = Auth::id();
            $rate->book_id = $book->id;
            $rate->star = $request->star;
            $rate->save();
        }

        //لحساب ريت الكتاب الكلي
        $rate_sum = 0;
        $count = 0;

        $books = Rate::where('book_id',$book->id)->get();
        foreach ($books as $bk) {
            $rate_sum += $bk->star;
            $count += 1;
        }
        $book->update([
            $book->rate = $rate_sum / $count
        ]);

        return $this->okResponse($rate, 'Rate added succesfully');
    }

    //اظهار تقييم شخص على كتاب
    public function show(LibraryBook $book)
    {
        $rate = Rate::where('user_id', Auth::id())->where('book_id', $book->id)->get();
        return $this->okResponse($rate, '');
    }

    public function top_rated()
    {
        $books = Book::join('library_books', 'library_books.book_id', 'books.id')
            ->orderBy('rate', 'DESC')
            ->get('library_books.*');
        return $this->okResponse(BookResource::collection(
            $books
        ), 'top rated books');
    }
}
