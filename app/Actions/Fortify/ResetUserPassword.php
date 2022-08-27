<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\{DB, Hash, Validator};
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    public function reset($user, array $input)
    {
        $validator = Validator::make($input, [
            'email'     => "required|email|exists:users",
            'password'  =>   'required|min:8|confirmed',
            'password_confirmation' => 'required',
            //'password' => $this->passwordRules(),
        ]);
        if($validator->fails()):
            return response([
                'message' => $validator->errors()->first(),
                'error' => $validator->getMessageBag()->toArray()
            ], 422);
        endif;

        $user->forceFill([
            'password' => $input['password'],
        ])->save();

        DB::table('password_resets')
        ->where([
            'email' => $input['email']
        ])->delete();
    }
}
