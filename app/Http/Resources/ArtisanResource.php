<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ArtisanResource extends JsonResource
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
            "firstname" => DB::table("users")->where('id',$this->user_id)->pluck('firstname')->first(),
            "lastname" => DB::table("users")->where('id',$this->user_id)->pluck('lastname')->first(),
            "skill" => $this->skill,
            "rating" => $this->rating,
            "town" => $this->town,
            "photo" => $this->photo,
            "completed_jobs" => 28,
            "bio" => DB::table("user_profiles")->where('user_id',$this->user_id)->pluck('bio')->first(),
            "distance" => isset($this->distance) ? $this->distance : 0,
            "longitude" => $this->longitude,
            "latitude" => $this->latitude,
            "workphotos" => DB::table('work_photos')
                                ->where(['user_id' => $this->user_id])
                                    ->get(),
            "reviews" => User::find($this->user_id)->reviews
        ];
    }
}
