<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminInformationResource;
use App\Http\Resources\BookResource;
use App\Models\AdminInformation;
use App\Models\Book;
use App\Models\LibraryBook;
use App\Models\RecentSearch;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    use ApiResponder;

    public function __construct()
    {
        $this->middleware('auth:api')->except(
            'librarySearch',
            'mostSearchedBooks'
        );
    }

    public function bookSearch($title)
    {
        $resaults = Book::where('name', $title)->get(['id','searches']);

        $recent_search = RecentSearch::where('user_id', Auth::id())->where('title', $title)->first();

        if (!is_null($recent_search)) {
            $recent_search->delete();
            $recent_search = new RecentSearch();
            $recent_search->user_id = Auth::id();
            $recent_search->title = $title;
            $recent_search->save();
        }else{
            $recent_search = new RecentSearch();
            $recent_search->user_id = Auth::id();
            $recent_search->title = $title;
            $recent_search->save();
        }

        $response = [];
        $i = 0;

        foreach ($resaults as $resault) {
            $resault->update([$resault->searches += 1]);
            $response[$i] = $resault->id;
            $i += 1;
        }

        return $this->okResponse(BookResource::collection(
            LibraryBook::whereIn('book_id', $response)->where('deleted_at',null)->get()
        ), 'book search resault');
    }

    public function librarySearch($title)
    {
        $resault = AdminInformation::where('library_name', $title)->first();
        return $this->okResponse(new AdminInformationResource($resault), 'library search resault');
    }

    public function mostSearchedBooks()
    {
        $books = Book::orderBy('searches', 'Desc')->distinct()->get('name');
        return $this->okResponse($books, 'most searched books');
    }

    public function recentSearches()
    {
        $recent_searches = RecentSearch::where('user_id', Auth::id())
            ->orderBy('created_at', 'Desc')
            ->get('title');
        return $this->okResponse($recent_searches, 'recent searches books');
    }

    public function clearRecentSearches()
    {
        RecentSearch::where('user_id', Auth::id())->delete();
        return $this->okResponse(null, 'recent searches books deleted successfully');
    }
}
