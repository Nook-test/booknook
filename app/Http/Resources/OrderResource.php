<?php

namespace App\Http\Resources;

use App\Models\Address;
use App\Models\AdminInformation;
use App\Models\CustomerInformation;
use App\Models\LibraryBook;
use App\Models\Offer;
use App\Models\OrderStatus;
use App\Models\SubOrder;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = CustomerInformation::where('user_id',$this->user_id)->first();
        $library = AdminInformation::where('user_id',$this->library_id)->first();
        $address = Address::findOrFail($this->address_id);
        $order_status = OrderStatus::findOrFail($this->status_id);
        $sub_orderes = SubOrder::where('order_id',$this->id)->get();
        return [
            'id' => $this->id,
            'user' => new CustomerInformationResource($user),
            'library' => new AdminInformationResource($library),
            'status' => $order_status->status,
            'address' => $address,
            'sub_orders' => SubOrderResource::collection($sub_orderes)
        ];
    }
}
