<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminInformationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $admin = User::findOrFail($this->user_id);

        return [
            'id' => $this->user_id,
            'role_id' => $admin->role_id,
            'email' => $admin->email,
            'is_verified' => $admin->is_verified,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'library_name' => $this->library_name,
            'phone_number' => $this->phone,
            'image' => $this->image,
            'status' => $this->status,
            'open_time' => $this->open_time,
            'close_time' => $this->close_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
