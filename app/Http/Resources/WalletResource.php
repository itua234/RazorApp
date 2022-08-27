<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            "id" => $this->id,
            "user_id" => $this->user_id,
            "balance" => $this->balance,
            "available_balance" => $this->available_balance,
            "has_set_pin" => $this->has_set_pin,
            "bank_name" => $this->bank_name,
            "account_name" => is_null($this->account_name) ? $this->account_name : Crypt::decryptString($this->account_name),
            "account_number" => is_null($this->account_number) ? $this->account_number : Crypt::decryptString($this->account_number),
            "bank_code" => $this->bank_code
        ];
    }
}
