<?php

namespace App\Http\Resources;

use App\Models\AdminInformation;
use App\Models\BorrowProcess;
use App\Models\CustomerInformation;
use App\Models\LibraryBook;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class BorrowBookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $order = Order::findOrFail($this->order_id);
        $book = LibraryBook::findOrFail($this->book_id);
        $user = CustomerInformation::where('user_id', $order->user_id)->first();
        $library = AdminInformation::where('user_id', $order->library_id)->first();
        if ($this->returned == 0) {
            $remaining_days = 30 - now()->diffInDays($this->created_at);
        } else {
            $remaining_days = 'returned';
        }
        return [
            'user' => new CustomerInformationResource($user),
            'library' => new AdminInformationResource($library),
            'book' => new BookResource($book),
            'remaining days' => $remaining_days
        ];
    }
}
