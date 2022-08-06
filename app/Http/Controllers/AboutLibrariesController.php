<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminInformationResource;
use App\Http\Resources\BookResource;
use App\Models\AdminInformation;
use App\Models\Book;
use App\Models\LibraryBook;
use App\Models\User;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;

class AboutLibrariesController extends Controller
{
    //
    use ApiResponder;

    //عرض كل المكاتب
    public function allLib()
    {
        return $this->okResponse(
            AdminInformationResource::collection(AdminInformation::all()),
            'All libraries'
        );
    }

    //عرض كل كتب مكتبة
    public function booksInLib(User $library)
    {
        // $lib = User::findOrFail($library);
        $books_id = Book::where('library_id', $library->id)->get('id');
        $response = [];
        $i = 0;
        foreach ($books_id as $book) {
            $response[$i] = $book->id;
            $i += 1;
        }
        $books = LibraryBook::whereIn('book_id',$response)->where('deleted_at',null)->get();
        return $this->okResponse(BookResource::collection($books), 'all books in this library');
    }
}
