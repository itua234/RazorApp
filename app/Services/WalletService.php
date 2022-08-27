<?php 

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Util\{CustomResponse, Fluidcoin};
use App\Models\{Transaction, User, Adresses};
use App\Http\Requests\{ResolveAccount};
use App\Http\Resources\{BankResource, WalletResource};
use Illuminate\Support\Facades\{DB, Http, Crypt, Hash, Mail};
class WalletService
{
    public function resolveAccount(ResolveAccount $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        
        try{
            $fluidcoin = new Fluidcoin;
            $response = $fluidcoin->resolve(
                $request->account,
                $request->bank_code
            );
            
            if($response['status'] == true):
                $bank = $fluidcoin->getBank($request->bank_code);
                
                $wallet->bank_code = $request->bank_code;
                $wallet->bank_name = $bank;
                $wallet->account_number = Crypt::encryptString($response['data']["account_number"]);
                $wallet->account_name = Crypt::encryptString($response['data']["account_name"]);
                $wallet->save();
                
                return CustomResponse::success($response['message'], new WalletResource($wallet));
            else:
                return CustomResponse::error($response['message'], 422);
            endif;

        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
    }

    public function getWallet()
    {
        $user = auth()->user();
        try{
            $wallet = User::find($user->id)->wallet;
            if(!is_null($wallet->account_number) && !is_null($wallet->bank_code)):
                $wallet->account_name = Crypt::decryptString($wallet->account_name);
                $wallet->account_number = Crypt::decryptString($wallet->account_number);
            endif;
           
            $transactions = Wallet::find($wallet->id)->transactions()
                ->orderBy('updated_at', 'DESC')
                    ->get();
            foreach($transactions as $array):
                $array->amount = number_format($array->amount);
                $array->updated = $array->updated_at->toFormattedDateString();
                unset($array->updated_at);
            endforeach;
        
            $wallet->allTransactions = $transactions;
            return CustomResponse::success('successful', $wallet);
        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
    }

    public function fetchBanks()
    {
        try{
            $payment = new Payment;
            $response = $payment->getBankList();
            
            $data = BankResource::collection($response["data"]);
            return CustomResponse::success('successful', $data);
        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
    }

    public function checkUserBankDetails()
    {
        $user = auth()->user();
        $wallet = new WalletResource(User::find($user->id)->wallet);
        try{
            if(is_null($wallet->account_number) && is_null($wallet->bank_code)):
                $message = "Account Details not found";
                return CustomResponse::error($message, 404);
            endif;
        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
        return CustomResponse::success('Wallet details:', $wallet);
    }

    /*public function fetchHistory($user, $page): \Illuminate\Http\JsonResponse
    {
        try {
            $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'DESC')
            ->paginate(
                $page[1],
                ['*'],
                'page',
                $page[0]
            );

            return CustomResponse::success($transactions);
        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
    }*/

    public function swap()
    {

    }
}