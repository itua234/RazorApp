<?php

namespace App\Actions\Fortify;

use Carbon\Carbon;
use App\Util\Helper;
use App\Mail\VerifyAccountMail;
use App\Models\{User, ReferralCode};
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Support\Facades\{DB, Mail};

class CreateNewUser implements CreatesNewUsers
{
    /**
     *create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'firstname' => $input['firstname'],
                'lastname' => $input['lastname'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'password' => $input['password'],
            ]), function (User $user) use ($input) {
                //if($input['user_type'] != "admin"):
                    /*$user->referralCode()->create([
                        'code' => Helper::generateReferral($user->firstname),
                    ]);

                    if(isset($input['referral_code'])):
                        $code = ReferralCode::where('code', $input['referral_code'])->first();
                        if($code):
                            $redeemRes = $code->redeem($user->id);
                        endif;
                    endif;*/

                    $code = mt_rand(10000, 99999);
                    DB::table('user_verification')
                    ->insert([
                        'email' => $user->email, 
                        'code' => $code, 
                        'expiry_time' => Carbon::now()->addMinutes(6)
                    ]);

                    /*Mail::to($user->email)
                        ->send(new VerifyAccountMail($user, $code));*/
                //endif;
            });
        });
    }
}
