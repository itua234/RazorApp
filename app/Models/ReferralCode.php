<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCode extends Model
{
    use HasFactory, BelongsToUser, SoftDeletes;

    protected $fillable = [
        'user_id',
        'code'
    ];


    public function redeem($userID)
    {
        if($this->user_id == $userID){
            $status = false;
            $message = 'You cannot redeem your referral code';
        }else{
            $hasRedeemed = ReferralCodeUsage::where([
                ['redeemer_id', '=', $userID],
                ['owner_id', '=', $this->user_id]
            ])->first();

            if(!$hasRedeemed){
                $status = true;
                $message = 'Referral Code Successfully redeemed';

                /*$owner = User::with('wallet')->find($this->user_id);

                $prevCredit = 0;

                if ($owner->goCredit != null) {
                    $prevCredit = $owner->goCredit->credits;
                }

                $newCredits = $prevCredit + 100;

                $owner->wallet()->updateOrCreate(
                    ['user_id' => $owner->id],
                    ['credits' => $newCredits]
                );*/

                ReferralCodeUsage::create([
                    'redeemer_id' => $userID,
                    'owner_id' => $this->user_id
                ]);
            }else{
                $status = false;
                $message = 'This code has already been redeemed by you';
            }
        }

        return [
            'status' => $status,
            'message' => $message,
        ];
    }


}
