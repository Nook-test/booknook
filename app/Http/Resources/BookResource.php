<?php

namespace App\Http\Resources;

use App\Http\Controllers\ShortcutController;
use App\Models\AdminInformation;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $book = Book::findOrFail($this->book_id);
        $library = AdminInformation::where('user_id',$book->library_id)->first();
        $categories = (new ShortcutController)->categories($this->book_id);
        $authors = (new ShortcutController)->authors($this->book_id);
        return [
            'id' => $this->id,
            'book_id' => $this->book_id,
            'library' => new AdminInformationResource($library),
            'name' => $book->name,
            'num_of_page' => $book->num_of_page,
            'summary' => $book->summary,
            'pdf' => $book->pdf,
            'image' => $book->image,
            'searches' => $book->searches,
            'rate' => $this->rate,
            'categories' => CategoryResource::collection($categories),
            'authors' => AuthorResource::collection($authors),
            'purchasing_price' => $this->purchasing_price,
            'selling_price' => $this->selling_price,
            'state' => $this->state,
            'quantity' => $this->quantity,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
