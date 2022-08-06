<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerInformationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $customer = User::findOrFail($this->user_id);

        return [
            'user_id' => $this->user_id,
            'role_id' => $customer->role_id,
            'email' => $customer->email,
            'is_verified' => $customer->is_verified,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'birth_day' => $this->birth_day,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
