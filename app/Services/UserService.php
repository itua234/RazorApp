<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Util\CustomResponse;
use App\Http\Resources\UserResource;
use App\Models\{User, BankAccount};
use App\Util\Fluidcoin;
use App\Http\Requests\{ SavePhoto};
use Illuminate\Support\Facades\{DB, Mail, Hash, Http, Validator};

class UserService
{
    public function updateProfilePhoto(SavePhoto $request)
    {
        $user = auth()->user();
        try{
            if($request->hasFile('photo')):
                $photo = $request->file('photo');
                $response = \Cloudinary\Uploader::upload($photo);
                $url = $response["url"];
                $user->profile_photo_path = $url;
                $user->save();
            endif;
            
            return CustomResponse::success("Profile photo path:", $url);
        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
    }

    public function saveProfileDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname'   =>  "required|max:50",
            'lastname'     =>  "required|max:70",
            'email'     => "required|email|max:255|unique:users,email",
            'phone'     =>  "required|numeric|min:11|unique:users,phone",
        ]);
        if($validator->fails()):
            return response([
                'message' => $validator->errors()->first(),
                'error' => $validator->getMessageBag()->toArray()
            ], 422);
        endif;

        $user = auth()->user();
        $data = User::where(['id' => $user->id])
        ->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'phone' => $request->phone,
            'email' => $request->email,
        ]);

        $message = "Profile updated Successfully";
        return CustomResponse::success($message, $data);
    }

    public function resolveAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_number' => 'required|numeric|digits:10',
            'bank_code' => 'required|'
        ]);
        if($validator->fails()):
            return response([
                'message' => $validator->errors()->first(),
                'error' => $validator->getMessageBag()->toArray()
            ], 422);
        endif;

        $user = auth()->user();
        
        try{
            $fluidcoin = new Fluidcoin;
            $response = $fluidcoin->resolve(
                $request->account_number,
                $request->bank_code
            );
            
            if($response['status'] == true):
                $bank = $fluidcoin->getBank($request->bank_code);
                
                $account = BankAccount::create([
                    'user_id' => $user->id,
                    'bank_code' => $request->bank_code,
                    'account_number' => $request->account_number,
                    'account_name' => $response["account"]["name"],
                    'bank_name' => $bank
                ]);
                
                return CustomResponse::success($response['message'], $account);
            else:
                return CustomResponse::error($response['message'], 422);
            endif;

        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
    }

    public function deleteBankDetail($id)
    {
        $user = auth()->user();
        try{
            BankAccount::where(['id' => $id])->delete();
            return CustomResponse::success("successful", null);
        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
    }

    public function deleteUserAccount()
    {
        $user = auth()->user();
        try{
            User::where(['id' => $user->id])->delete();
            return CustomResponse::success("successful", null);
        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
    }

    public function verifyBVN($bvn)
    {
        $user = auth()->user();
        try{
            $url = 'https://vapi.verifyme.ng/v1/verifications/identities/bvn/'.$bvn;
            $response = Http::acceptJson()
                ->withToken($this->secretKey)
                    ->post($url, [
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname
                    ]);
            return $response;
        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
        
    }


    public function verifyNIN($nin)
    {
        $user = auth()->user();
        try{
            $url = 'https://vapi.verifyme.ng/v1/verifications/identities/nin/'.$nin;
            $response = Http::acceptJson()
                ->withToken($this->secretKey)
                    ->post($url, [
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname
                    ]);
            return $response;
        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
        
    }

}
