<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\WalletResource;
use App\Util\{CustomResponse, Payment};
use App\Models\{Transaction, User, Wallet};
use App\Http\Requests\{InitiateDeposit, TransferRequest};
use Illuminate\Support\Facades\{DB, Mail, Hash, Http, Crypt};

class TransactionService
{
    public function initiateDeposit(InitiateDeposit $request)
    {
        $user = auth()->user();
        $wallet = User::find($user->id)->wallet;
        $amount = $request->amount * 100;   //Convert to kobo
        try{
            $callback = "http://127.0.0.1:8000/api/v1/wallet/callback/";
            $payment = new Payment;
            $response = $payment->initiateDeposit(
                $user->email,
                $amount,
                $request->channel,
                $callback
            );

            $transaction = Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'Credit',
                'amount' => $request->amount,
                'reference' => $response['data']["reference"],
                'method'  => $request->channel
            ]);

            $data = [
                'email' => $user->email,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'amount' => $amount,
                'authorization_url' => $response['data']["authorization_url"],
                'access_code' => $response['data']["access_code"],
                'reference' => $response['data']["reference"],
                'callback_url' =>  $callback
            ];
            return CustomResponse::success($response['message'], $data);
        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
    }

    public function callback(Request $request)
    {
        $reference = $request->reference;
        try{
            $transaction = Transaction::where(['reference' => $reference])->first();
            if (!$transaction) exit();
            if ($transaction->verified) exit();

            $payment = new Payment;
            $paymentDetails = $payment->getPaymentData($reference);
            $amount = $paymentDetails['data']["amount"];
            $amount = $amount / 100;

            $transaction->status = "success";
            $transaction->verified = 1;
            $transaction->save();

            $wallet = Wallet::find($transaction->wallet_id);
            $wallet->balance += $amount;
            $wallet->available_balance += $amount;
            $wallet->save();

            return CustomResponse::success("success", $wallet);
        }catch (\Exception $e) {
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
    }

    public function escapeWebView(Request $request)
    {}

    public function transfer(TransferRequest $request)
    {
        $user = auth()->user();
        $wallet = User::find($user->id)->wallet;
        
        if($wallet->available_balance < $request->amount):
            $message = "Insufficient Balance";
            return CustomResponse::error($message, 400);
        endif;

        try{
            $payment = new Payment;
            $recipient = $payment->createTransferRecipient(
                Crypt::decryptString($wallet->account_number),
                Crypt::decryptString($wallet->account_name),
                $wallet->bank_code
            );

            $wallet->available_balance -= $request->amount;
            $wallet->save();
            $reference = $payment->generateReference($user->id);
            $transaction = Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'Debit',
                'amount' => $request->amount,
                'reference' => $reference,
                'method'  => 'Bank Transfer'
            ]);
            
            return $payment->sendMoney(
                "balance",
                $request->amount * 100,  //Conversion to kobo 
                $recipient['data']["recipient_code"],
                'Workpro Withdrawal Testing thursday',
                $reference
            );
            //return CustomResponse::success($response['message'], $data);
        }catch(\Exception $e){
            $message = $e->getMessage();
            return CustomResponse::error($message);
        }
    }

    public function transferWebhook(Request $request)
    {
        http_response_code(200);

        $transaction = Transaction::where([
            'reference' => $request['data']["reference"] ])
                ->first();
        if (!$transaction) exit();
        if ($transaction->verified) exit();

        $wallet = Wallet::find($transaction->wallet_id);

        if ($request['event'] == "transfer.success"):
            Transaction::where(['id' => $transaction->id])
            ->update([
                'status' => 'success',
                'verified' => 1
            ]);
            
            $wallet->balance -= $request['data']["amount"] / 100;
            $wallet->save();
        elseif ($request['event'] == "transfer.failed"):
            Transaction::where(['id' => $transaction->id])
            ->update([
                'status' => 'failed',
                'verified' => 1
            ]);
            
            $wallet->available_balance += $request['data']["amount"] / 100;
            $wallet->save();
        elseif ($request['event'] == "transfer.reversed"):
            Transaction::where(['id' => $transaction->id])
            ->update([
                'status' => 'reversed',
                'verified' => 1
            ]);
            
            $wallet->available_balance += $request['data']["amount"] / 100;
            $wallet->save();
        endif;
        
        exit();
    }
    
}
