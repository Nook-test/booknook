<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use App\Models\AdminInformation;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\LibraryBook;
use App\Traits\ApiResponder;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    //
    use ApiResponder;

    public function __construct()
    {
        $this->middleware('auth:api')->only(
            'store',
            'update',
            'destroy'
        );
    }

    public function getPdfPath(UploadedFile $pdf)
    {
        $pdf_name =   time() . '.' . $pdf->getClientOriginalExtension();
        $pdf->storeAs('public/pdfs', $pdf_name);
        return 'storage/pdfs/' . $pdf_name;
    }

    //كل الكتب في كل المكاتب
    public function index()
    {
        return $this->okResponse(
            BookResource::collection(LibraryBook::where('deleted_at',null)->get()),
            'All Books'
        );
    }

    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'category' => 'required|array',
            'author' => 'required|array',
            'num_of_page' => 'required|numeric',
            'purchasing_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'state' => 'required',
            'quantity' => 'required|numeric',
            'pdf',
            // 'pdf' => 'required|file',
            'summary' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }
        // return response($request['category']);

        // $path = $this->getPdfPath($request->file('pdf'));

        if (Book::where('name', $request->name)->where('library_id', Auth::id())->exists()) {

            $book = Book::where('name', $request->name)->where('library_id', Auth::id())->first();

            if (LibraryBook::where('book_id', $book->id)->where('state', $request->state)->where('deleted_at', null)->exists())
                return $this->okResponse(null, 'this book is already exists in your library. you can update information of this book if you need!');
            else if (LibraryBook::where('book_id', $book->id)->where('state', $request->state)->where('deleted_at', '!=', null)->exists()) {
                $library_book = LibraryBook::where('book_id', $book->id)->where('state', $request->state)->where('deleted_at', '!=', null)->first();

                $library_book->update([
                    $library_book->deleted_at = null,
                    $library_book->quantity = $request->quantity,
                    $library_book->purchasing_price = $request->purchasing_price,
                    $library_book->selling_price = $request->selling_price,
                ]);

                return $this->okResponse(
                    new BookResource(LibraryBook::findOrFail($library_book->id)),
                    'book added successfully to your Library.'
                );
            } else {

                $library_book = new LibraryBook();
                $library_book->book_id = $book->id;
                $library_book->state = $request->state;
                $library_book->quantity = $request->quantity;
                $library_book->purchasing_price = $request->purchasing_price;
                $library_book->selling_price = $request->selling_price;
                $library_book->save();

                return $this->okResponse(
                    new BookResource(LibraryBook::findOrFail($library_book->id)),
                    'book added successfully to your Library.'
                );
            }
        } else {
            $book = new Book();
            $book->library_id = Auth::id();
            $book->name = $request->name;
            $book->num_of_page = $request->num_of_page;
            $book->pdf = $request->pdf;
            $book->summary = $request->summary;
            $book->save();

            $authors = [];
            $categories = [];
            $i = 0;

            foreach ($request->category as $cat) {
                $category_id  = Category::where('name', $cat)->first();
                $categories[$i] = $category_id->id;
                $i += 1;
            }
            // return response($categories);
            $book->categories()->sync($categories);
            $i = 0;

            foreach ($request->author as $auth) {
                if (!Author::where('name', $auth)->exists()) {
                    $author = new Author();
                    $author->name = $auth;
                    $author->save();
                }
                $author_id = Author::where('name', $auth)->first();
                $authors[$i] = $author_id->id;
                $i += 1;
            }

            $book->authors()->sync($authors);

            $library_book = new LibraryBook();
            $library_book->book_id = $book->id;
            $library_book->state = $request->state;
            $library_book->quantity = $request->quantity;
            $library_book->purchasing_price = $request->purchasing_price;
            $library_book->selling_price = $request->selling_price;
            $library_book->save();

            return $this->okResponse(
                new BookResource(LibraryBook::findOrFail($library_book->id)),
                'book added successfully to your Library.'
            );
        }
    }

    public function update(Request $request, LibraryBook $book)
    {
        //
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'categories' => 'required|array',
            'authors' => 'required|array',
            'num_of_page' => 'required|numeric',
            'purchasing_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'state' => 'required|string',
            'quantity' => 'required|numeric',
            'pdf' => 'required',
            'summary' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }
        $bookIn = Book::find($book->book_id);
        if ($book->deleted_at == null) {
            if ($bookIn->name != $request->name) {
                //عم يعدل الاسم لاسم موجود مسبقا بمكتبته
                if (Book::where('name', $request->name)->where('library_id', Auth::id())->exists()) {
                    $existsBook = Book::where('name', $request->name)->where('library_id', Auth::id())->first();
                    $existsLibraryBook = LibraryBook::where('book_id', $existsBook->id)->where('deleted_at',null)->get();
                    foreach ($existsLibraryBook as $li) {
                        if ($li->state == $request->state) {
                            return $this->okResponse(null, 'the book with the same name and same state already exists in your library.
                             you can update other information in this book if you need!');
                        }
                    }

                    $authors = [];
                    $categories = [];
                    $i = 0;

                    foreach ($request['categories'] as $cat) {
                        $category_id  = Category::where('name', $cat)->first();
                        $categories[$i] = $category_id->id;
                        $i += 1;
                    }

                    $existsBook->categories()->sync($categories);
                    $i = 0;

                    foreach ($request['authors'] as $auth) {
                        if (!Author::where('name', $auth)->exists()) {
                            $author = new Author();
                            $author->name = $auth;
                            $author->save();
                        }
                        $author_id = Author::where('name', $auth)->first();
                        $authors[$i] = $author_id->id;
                        $i += 1;
                    }

                    $existsBook->authors()->sync($authors);

                    $existsBook->update([
                        $existsBook->num_of_page = $request->num_of_page,
                        $existsBook->pdf = $request->pdf,
                        $existsBook->summary = $request->summary
                    ]);
                    //كتاب موجود سابقا بمكتبته بس لحالة غير موجودة سابقا
                    $book->update([
                        $book->book_id = $existsBook->id,
                        $book->state = $request->state,
                        $book->quantity = $request->quantity,
                        $book->purchasing_price = $request->purchasing_price,
                        $book->selling_price = $request->selling_price,
                    ]);

                    return $this->okResponse(null, 'book information updateded successfully.');
                } else {
                    //عم يعدل الاسم لاسم غير موجود سابقا بمكتبته
                    $newbook = new Book();
                    $newbook->library_id = Auth::id();
                    $newbook->name = $request->name;
                    $newbook->num_of_page = $request->num_of_page;
                    $newbook->pdf = $request->pdf;
                    $newbook->summary = $request->summary;
                    $newbook->save();

                    $authors = [];
                    $categories = [];
                    $i = 0;

                    foreach ($request['categories'] as $cat) {
                        $category_id  = Category::where('name', $cat)->first();
                        $categories[$i] = $category_id->id;
                        $i += 1;
                    }

                    $newbook->categories()->sync($categories);
                    $i = 0;

                    foreach ($request['authors'] as $auth) {
                        if (!Author::where('name', $auth)->exists()) {
                            $author = new Author();
                            $author->name = $auth;
                            $author->save();
                        }
                        $author_id = Author::where('name', $auth)->first();
                        $authors[$i] = $author_id->id;
                        $i += 1;
                    }

                    $newbook->authors()->sync($authors);

                    $book->update([
                        $book->book_id = $newbook->id,
                        $book->state = $request->state,
                        $book->quantity = $request->quantity,
                        $book->purchasing_price = $request->purchasing_price,
                        $book->selling_price = $request->selling_price,
                    ]);

                    return $this->okResponse(null, 'book information updateded successfully.');
                }
            } else { //نفس الاسم

                $changed = 0;
                //نفس الاسم ونفس الحالة
                if ($book->state == $request->state) {
                    $book->update([
                        $book->quantity = $request->quantity,
                        $book->purchasing_price = $request->purchasing_price,
                        $book->selling_price = $request->selling_price,
                    ]);
                    $changed = 1;
                }

                if ($changed == 0) {
                    //نفس الاسم بس لغير حالة منشيك اذا الحالة موجودة من قبل ولا لا
                    $library_books = LibraryBook::where('book_id', $bookIn->id)->get();
                    foreach ($library_books as $li) {
                        if ($li->state == $request->state) {
                            return $this->okResponse(null, 'the book with the same name and same state already exists in your library. you can update other information in this book if you need!');
                        }
                    }

                    //نفس الاسم لحالة غير موجودة
                    $book->update([
                        $book->state = $request->state,
                        $book->quantity = $request->quantity,
                        $book->purchasing_price = $request->purchasing_price,
                        $book->selling_price = $request->selling_price,
                    ]);
                }


                $authors = [];
                $categories = [];
                $i = 0;

                foreach ($request['categories'] as $cat) {
                    $category_id  = Category::where('name', $cat)->first();
                    $categories[$i] = $category_id->id;
                    $i += 1;
                }

                $bookIn->categories()->sync($categories);
                $i = 0;

                foreach ($request['authors'] as $auth) {
                    if (!Author::where('name', $auth)->exists()) {
                        $author = new Author();
                        $author->name = $auth;
                        $author->save();
                    }
                    $author_id = Author::where('name', $auth)->first();
                    $authors[$i] = $author_id->id;
                    $i += 1;
                }

                $bookIn->authors()->sync($authors);

                $bookIn->update([
                    $bookIn->num_of_page = $request->num_of_page,
                    $bookIn->pdf = $request->pdf,
                    $bookIn->summary = $request->summary
                ]);

                return $this->okResponse(null, 'book information updateded successfully.');
            }
        }
    }

    public function show(LibraryBook $book)
    {
        return $this->okResponse(
            new BookResource(
                $book
            ),
            'This book Details'
        );
    }

    public function destroy(LibraryBook $book)
    {
        $book->delete();
        return $this->okResponse(null, 'book deleted successfully');
    }
}
