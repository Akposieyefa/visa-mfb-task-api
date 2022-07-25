<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'role' => $this->role,
            'slug' => $this->slug,
            'relationships' => [
                'wallet' => $this->wallet,
                'transactions' => $this->transactions
            ],
            'created_at'=> $this->created_at,
            'updated_at'=> $this->updated_at
        ];
    }
}
