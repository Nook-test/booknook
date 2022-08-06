<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfferResource;
use App\Models\AdminInformation;
use App\Models\BookOffer;
use App\Models\LibraryBook;
use App\Models\Offer;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller
{
    use ApiResponder;

    public function __construct()
    {
        $this->middleware('auth:api')->only(
            'store',
            'update',
            'destroy'
        );
    }

    public function indexOneLib(AdminInformation $library)
    {
        $offers = Offer::where('library_id', $library->id)->where('deleted_at', null)->get();
        return $this->okResponse(
            OfferResource::collection($offers),
            'All Offers in this library'
        );
    }

    public function indexAllLib()
    {
        $offers = Offer::where('deleted_at', null)->get();
        return $this->okResponse(
            OfferResource::collection($offers),
            'All Offers'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'totalPrice' => 'required|numeric',
            'books' => 'required|array',
            'quantity' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }

        if (
            !Offer::where('title', $request->title)->where('library_id', Auth::id())->exists() // غير موجود مسبقا
            || Offer::where('title', $request->title)->where('library_id', Auth::id())->where('deleted_at', '!=', null)->exists() //موجود سابقا بس محذوف
        ) {
            $offer = new Offer();
            $offer->title = $request->title;
            $offer->library_id = Auth::id();
            $offer->totalPrice = $request->totalPrice;
            $offer->quantity = $request->quantity;
            $offer->save();

            $offer->books()->sync($request['books']);
            $offer_books = BookOffer::where('offer_id', $offer->id)->get('book_id');

            foreach ($offer_books as $book) {
                (new ShortcutController)->decrease_quantity('book', $book->book_id, $request->quantity);
            }

            return $this->okResponse(null, 'offer added successfully');
        } else {
            return $this->okResponse(null, 'offer title must be unique in your library');
        }
    }

    public function show(Offer $offer)
    {
        return $this->okResponse(new OfferResource($offer), 'this Offer details');
    }

    public function update(Request $request, Offer $offer)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string',
            'totalPrice' => 'numeric',
            'books' => 'array',
            'quantity' => 'numeric'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }
        //اذا اسم العرض غير موجود سابقا بالمكتبة او اذا موجود بس محذوف
        if (
            $request->has('title') &&
            (!Offer::where('title', $request->title)->where('library_id', Auth::id())->exists() // غير موجود مسبقا
                || Offer::where('title', $request->title)->where('library_id', Auth::id())->where('deleted_at', '!=', null)->exists())
        ) {
            $offer->title = $request->title;
        }

        if ($request->has('totalPrice')) {
            $offer->totalPrice = $request->totalPrice;
        }

        if ($request->has('quantity')) {
            $offer->quantity = $request->quantity;
        }

        if ($request['books']) {
            //منرجع منزيد كمية العرض عكمية الكتب بالمكتبة لاحتمال اذا مغير كتب العرض
            $offer_books = BookOffer::where('offer_id', $offer->id)->get('book_id');
            foreach ($offer_books as $book) {
                (new ShortcutController)->increase_quantity('book', $book->book_id, $offer->quantity);
            }
            //منحدد كتب العرض الجديدة
            $offer->books()->sync($request['books']);
            $offer_books = BookOffer::where('offer_id', $offer->id)->get('book_id');

            //مننقص كمية العرض من كمية الكتب الموجودة بالعرض
            foreach ($offer_books as $book) {
                if ($request->has('quantity')) {
                    (new ShortcutController)->decrease_quantity('book', $book->book_id, $request->quantity);
                }else{
                    (new ShortcutController)->decrease_quantity('book', $book->book_id, $offer->quantity);
                }
            }
        }

        $offer->save();

        return $this->okResponse(null, 'offer updated successfully');
    }

    public function destroy(Offer $offer)
    {
        $offer_books = BookOffer::where('offer_id', $offer->id)->get();
        foreach ($offer_books as $offer_book) {
            (new ShortcutController)->increase_quantity('book', $offer_book->book_id, $offer->quantity);
        }
        $offer->delete();
        return $this->okResponse(null, 'offer deleted successfully');
    }
}
