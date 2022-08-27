<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;

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
            'id' => $this->id,
            "firstname" => $this->firstname,
            "lastname" => $this->lastname,
            "email" => $this->email,
            "phone" => $this->phone,
            "wallet" => new WalletResource(User::find($this->id)->wallet),
            "profile" => new ProfileResource(User::find($this->id)->profile),
            "nok" => User::find($this->id)->nok,
            "posts" => new PostResource(User::find($this->id)->jobposts)
        ];
    }
}
