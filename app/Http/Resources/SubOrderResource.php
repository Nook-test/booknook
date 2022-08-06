<?php

namespace App\Http\Resources;

use App\Models\LibraryBook;
use App\Models\Offer;
use App\Models\SubOrder;
use Illuminate\Http\Resources\Json\JsonResource;

class SubOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $book = null;
        $offer = null;
        if($this->type == 'book'){
            $book = LibraryBook::findOrFail($this->book_id);
            $book = new BookResource($book);
        }else{
            $offer = Offer::findOrFail($this->offer_id);
            $offer = new OfferResource($offer);
        }
        return [
            'book' => $book,
            'offer' => $offer,
            'quantity' => $this->quantity
        ];
    }
}
