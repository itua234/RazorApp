<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Util\CustomResponse;
use App\Http\Resources\UserResource;
use App\Mail\{VerifyAccountMail, SendCodeMail, ForgetPasswordMail};
use App\Models\{User};
use App\Http\Requests\{LoginRequest, VerifyAccount, 
    ResetPassword, ChangePassword, CreateUser};
use App\Actions\Fortify\{CreateNewUser, ResetUserPassword};
use Illuminate\Support\Facades\{DB, Mail, Hash, Http, Validator};

class AuthService
{
    public function login(LoginRequest $request)
    {
        try{
            $user = User::where("email", $request->email)->first();
            if(!$user || !password_verify($request->password, $user->password)):
                $message = "Wrong credentials";
                return CustomResponse::error($message, 400);
            elseif((int)$user->is_verified !== 1):
                $message = "Email address not verified, please verify your email before you can login";
                return CustomResponse::error($message, 401);
            endif;
            
            $token = $user->createToken("RazorWallet")->plainTextToken;
            $user->token = $token;
            $message = 'Login successfully';
            return CustomResponse::success($message, $user);
        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
    }

    public function register(CreateUser $request)
    {
        try{
            $createUser = new CreateNewUser;
            $user = $createUser->create($request->input());

            $token = $user->createToken("RazorWallet")->plainTextToken;
            $user->token = $token;
        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }

        $message = 'Thanks for signing up! Please check your email to complete your registration.';
        return CustomResponse::success($message, $user, 201);
    }

    public function requestTokenGoogle(Request $request)
    {
        //Getting the user from socialite using token from google
        $user = Socialite::driver('google')->stateless()->userFromToken($request->token);

        //Getting or creating user from db
        $userFromDb = User::firstOrCreate([
            'email' => $user->getEmail()
        ],[
            'email_verified_at' => Carbon::now(),
            'is_verified' => 1,
            'status' => '1',
            'firstname' => $user->offsetGet('given_name'),
            'lastname' => $user->offsetGet('family_name'),
            'phone' => NULL
        ]);

        $token = $userFromDb->createToken("RazorWallet")->plainTextToken;
        $userFromDb->token = $token;
        $message = 'Google Login successful';
        return CustomResponse::success($message, $userFromDb);
    }

    public function logout()
    {
        auth()->user()->tokens->each(function ($token, $key) {
            $token->delete();
        });

        return CustomResponse::success("User has been logged out", null);
    }

    public function refresh()
    {
        $user = auth()->user();

        $user->tokens->each(function ($token, $key) {
            $token->delete();
        });

        $token = $user->createToken("RazorWallet")->plainTextToken;

        return CustomResponse::success("token refreshed successfully", $token);
    }

    public function sendverificationcode($email)
    {   
        try{
            $user = User::where(['email' => $email])->first();
            $code = mt_rand(10000, 99999);

            $isTokened = DB::table('user_verification')
            ->where(['email' => $user->email])->first();
            if($isTokened):
                DB::table('user_verification')
                ->where(['email' => $user->email])
                ->update([
                    'code' => $code, 
                    'expiry_time' => Carbon::now()->addMinutes(6)
                ]);
            else:
                DB::table('user_verification')
                ->insert([
                    'email' => $user->email, 
                    'code' => $code, 
                    'expiry_time' => Carbon::now()->addMinutes(6)
                ]);
            endif;

            Mail::to($user->email)
                ->send(new VerifyAccountMail($user, $code));
            $message = 'A new verification code has been sent to your email.';
        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
        return CustomResponse::success($message, null);
    }

    public function verifyUser(VerifyAccount $request)
    {
        $check = DB::table('user_verification')
        ->where([
            'email' => $request->email, 
            'code' => $request->code
        ])->first();
        $current_time = Carbon::now();
        try{
            switch(is_null($check)):
                case(false):
                    if($check->expiry_time < $current_time):
                        $message = 'Verification code is expired';
                    else:
                        $user = User::where('email', $check->email)->first();
                        User::where('id', $user->id)
                        ->update([
                            'is_verified' => 1, 
                            'email_verified_at' => $current_time
                        ]);

                        DB::table('user_verification')
                        ->where('email', $request->email)->delete();

                        $message = 'Your email address is verified successfully.';
                        return CustomResponse::success($message, null);
                    endif;
                break;
                default:
                    $message = "Verification code is invalid.";
            endswitch;
        }catch(\Exception $e){
            $error_message = $e->getMessage();
            return CustomResponse::error($error_message);
        }
        return CustomResponse::error($message);
    }

    public function verifyUserByLink(VerifyAccount $request)
    {
        $check = DB::table('user_verification')
        ->where([
            'email' => $request->email, 
            'code' => $request->code
        ])->first();
        $current_time = Carbon::now();

        switch(is_null($check)):
            case(false):
                if($check->expiry_time < $current_time):
                    return view('auth.verify-email')->with('');
                else:
                    $user = User::where('email', $check->email)->first();
                    User::where('id', $user->id)
                    ->update([
                        'is_verified' => 1, 
                        'email_verified_at' => $current_time
                    ]);

                    DB::table('user_verification')
                    ->where('email', $request->email)->delete();

                    $message = 'Your email address is verified successfully.';

                    return view('auth.verify-email')->with('');
                endif;
            break;
            default:
                $message = "Verification code is invalid.";
                return view('auth.verify-email')->with('');
        endswitch;
    }

    public function resetPassword(ResetPassword $request)
    {
        $user = User::where(['email' => $request->email])->first();
        $check = DB::table('password_resets')
        ->where([
            'email' => $user->email
        ])->first();
        $token = mt_rand(1000, 9999);
        $expiry_time = Carbon::now()->addMinutes(6);

        try{
            switch(is_null($check)):
                case(false):
                    DB::table('password_resets')
                    ->where(['email' => $check->email])
                    ->update([
                        'token' => $token, 
                        'created_at' => $expiry_time
                    ]);
                    $message = 'A new password reset email has been sent! Please check your email.';
                break;
            default:
                DB::table('password_resets')
                ->insert([
                    'email' => $user->email, 
                    'token' => $token, 
                    'created_at' => $expiry_time
                ]);
                $message = 'A password reset email has been sent! Please check your email.';
            endswitch;

            Mail::to($user->email)
                ->send(new ForgetPasswordMail($user, $token));
        }catch(\Exception $e){
            $error_message = $e->getMessage();
            return CustomResponse::error($error_message);
        }

        return CustomResponse::success($message, null);
    }

    public function verifyResetToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|numeric|exists:password_resets'
        ]);
        if($validator->fails()):
            return CustomResponse::error(
                $validator->errors()->first(),
                $validator->getMessageBag()->toArray(), 422
            );
        endif;

        $resetData = DB::table('password_resets')
        ->where([
            'token' => $request->token, 
            'email' => $request->email
        ])->first();
        if(!is_null($resetData)):
            if($resetData->created_at > Carbon::now()):
                $message = 'Your token verification was successful.';
                return CustomResponse::success($message, $resetData);
            else:
                $message = "Password reset token is expired.";
            endif;
        else:
            $message = "Invalid data.";
        endif;

        return CustomResponse::error($message, 400);
    }

    public function verifyResetTokenByLink(Request $request)
    {
        $validator = Validator::make($request, [
            'email' => 'required|email',
            'token' => 'required|numeric|exists:password_resets'
        ])->validate();

        $tokenedUser = DB::table('password_resets')
        ->where([
            'token' => $request->token, 
            'email' => $request->email
        ])->first();

        if(!is_null($tokenedUser)):
            if($tokenedUser->expiry_time > Carbon::now()):
                return view('auth.password-reset', [
                    'email' => $request->email
                ]);
            endif;
        endif;
    }

    public function password_reset(Request $request)
    {   
        try{
            $user = User::where(['email' => $request->email])->first();
            $resetUser = new ResetUserPassword;
            $reset = $resetUser->reset($user, $request->input());

            $message = 'Your password has been changed!';
        }catch(\Exception $e){
            $error_message = $e->getMessage();
            return CustomResponse::error($error_message);
        }

        return CustomResponse::success($message, null);
    }

    public function change_password(ChangePassword $request)
    {
        $user = auth()->user();
        try{
            if((Hash::check($request->current_password, $user->password)) == false):
                $message = "Check your old password.";
            elseif((Hash::check($request->password, $user->password)) == true):
                $message = "Please enter a password which is not similar to your current password.";
            else:
                $user->password = $request->password;
                $user->save();

                $message = "Your password has been changed successfully";
                return CustomResponse::success($message, null);
            endif;
        }catch(\Exception $e){
            $error_message = $e->getMessage();
            return CustomResponse::error($error_message);
        }
        
        return CustomResponse::error($message, 400);
    }

    public function saveFCMToken(Request $request)
    {
        $user = auth()->user();
        try{
            $user->fcm_token = $request->token;
            $user->save();

            $message = 'FCM token updated successfully';
        }catch(\Exception $e){
            $error_message = $e->getMessage();
            return CustomResponse::error($error_message);
        }
        return CustomResponse::success($message, null);
    }

}
