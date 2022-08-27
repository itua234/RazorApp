<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WalletService;
use App\Services\TransactionService;
use App\Http\Requests\{InitiateDeposit, TransferRequest, ResolveAccount, WalletPin};

class WalletController extends Controller
{
    protected WalletService $walletService;
    protected TransactionService $transactionService; 

    public function __construct(
        WalletService $walletService,
        TransactionService $transactionService
    )
    {
        $this->walletService = $walletService;
        $this->transactionService = $transactionService;
    }

    public function transferWebhook(Request $request)
    {
        return $this->transactionService->transferWebhook($request);
    }

    public function transfer(TransferRequest $request)
    {
        return $this->transactionService->transfer($request);
    }
    

    
    public function resolveAccount(ResolveAccount $request)
    {
        return $this->walletService->resolveAccount($request);
    }

    public function getWallet()
    {
        return $this->walletService->getWallet();
    }

    public function fetchBanks()
    {
        return $this->walletService->fetchBanks();
    }

    public function checkUserBankDetails()
    {
        return $this->walletService->checkUserBankDetails();
    }

    public function setWalletPin(WalletPin $request)
    {
        return $this->walletService->setWalletPin($request);
    }

    public function checkWalletPin(WalletPin $request)
    {
        return $this->walletService->checkWalletPin($request);
    }

}
