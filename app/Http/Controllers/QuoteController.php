<?php

namespace App\Http\Controllers;

use App\Models\AdminInformation;
use App\Models\Book;
use App\Models\LibraryBook;
use App\Models\Quote;
use App\Models\User;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuoteController extends Controller
{
    use ApiResponder;

    public  function __construct()
    {
        $this->middleware('auth:api')->only(
            'store',
            'update',
            'destroy'
        );
    }

    public function index()
    {
        return $this->okResponse(Quote::all(), 'All qoutes');
    }

    public function index_on_book(LibraryBook $book)
    {
        $bk = Book::findOrFail($book->book_id);
        $quotes = Book::join('library_books', 'library_books.book_id', 'books.id')
            ->join('quotes', 'quotes.book_id', 'library_books.id')
            ->where('books.name', $bk->name)
            ->get(['quotes.*']);

        return $this->okResponse($quotes, 'All qoutes on this book');
    }

    public function store(Request $request, LibraryBook $book)
    {
        $validator = Validator::make($request->all(), [
            'quote' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }

        Quote::create([
            'quote' => $request->quote,
            'book_id' => $book->id,
            'user_id' => Auth::id(),
        ]);

        return $this->okResponse(null, 'Qoute added successfully');
    }

    public function show(Quote $quote)
    {
        return $this->okResponse($quote, '');
    }

    public function update(Request $request, Quote $quote)
    {
        $validator = Validator::make($request->all(), [
            'quote' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }

        $quote->update([
            'quote' => $request->quote
        ]);

        return $this->okResponse(null, 'quote updated successfully');
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();
        return $this->okResponse(null, 'delete is done');
    }
}
