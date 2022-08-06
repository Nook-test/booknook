<?php

namespace App\Http\Resources;

use App\Models\LibraryBook;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $response = [];
        $i = 0;
        foreach ($this->books as $book) {
            $response[$i] = $book->id;
            $i += 1;
        }
        $books = LibraryBook::whereIn('id',$response)->get();
        return [
            'id' => $this->id,
            'library_id' => $this->library_id,
            'title' => $this->title,
            'total_price' => $this->totalPrice,
            'quantity' => $this->quantity,
            'books' => BookResource::collection($books),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
