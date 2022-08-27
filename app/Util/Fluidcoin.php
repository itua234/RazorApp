<?php

namespace App\Util;

use Illuminate\Support\Facades\Http;

class Fluidcoin
{
    private $baseUrl;
    private $secretKey;

    public function __construct()
    {
        $this->secretKey = env('BPW_SECRET', '');
        $this->baseUrl = 'https://developers.bitpowr.com/api/v1/';
    }

    public function getPrice($currency)
    {
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($this->baseUrl."market/price",[
                    'currency' => $currency
                ]);
        return $response;
    }

    public function getMarketTicker()
    {
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($this->baseUrl."market/ticker");
        return $response;
    }

    public function createAddress(array $data)
    {
        $url = $this->baseUrl.'addresses/';
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->post($url, [
                    'label' => $data[0],
                    'asset' => $data[1],
                    'accountId' => $data[2],
                    //'addressType' => $addressType,
                    //'derivationIndex' => $derivationIndex
                ]);
        return $response;
    }

    public function fetchAddress()
    {
        //$reference is the address unique identifier e.g ADDR_xy
        $url = $this->baseUrl.'addresses/';
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($url, [
                    'assetId' => '',
                    'accountId' => '',
                    'subAccountId' => ''
                ]);
        return $response;
    }

    public function fetchAddressByAddressId($addressId)
    {
        $url = $this->baseUrl.'addresses/'.$addressId;
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($url,[
                    'addressId' => $addressId
                ]);
        return $response;
    }

    public function fetchAddressTransactions($addressId)
    {
        $url = $this->baseUrl.'addresses/'.$addressId.'/transactions';
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($url, [
                    'addressId' => $addressId
                ]);
        return $response;
    }

    public function fetchAddressBalance($addressId)
    {
        $url = $this->baseUrl.'addresses/'.$addressId.'/balance';
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($url, [
                    'addressId' => $addressId
                ]);
        return $response;
    }

    public function createAccount(array $data)
    {
        $url = $this->baseUrl.'accounts/';
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->post($url, [
                    'name' => $name,
                    'passphrase' => $password,
                    //'type' => 'DEFAULT',   ///cOULD BE DEFAULT/SELF_CUSTODY/SAVINGS/EXCHANGES
                    //'showInDashboard' => true, // could be true or false
                    'assets' => [],
                    //'externalId' => '',
                    //'email' => ''   //required only for self_custody type
                ]);
        return $response;
    }

    public function getAccountById($uid)
    {
        $url = $this->baseUrl.'accounts/'.$uid;
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($url, [
                    'uid' => $uid,
                ]);
        return $response;
    }

    public function getAccountAssets($id)
    {
        $url = $this->baseUrl.'accounts/'.$id.'/assets';
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($url, [
                    'id' => $id,
                ]);
        return $response;
    }

    public function addAssetToAccount($uid, $label, $asset)
    {
        $url = $this->baseUrl.'accounts/'.$uid.'/assets';
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->post($url, [
                    'uid' => $uid,
                    'label' => $label,
                    'asset' => $asset
                ]);
        return $response;
    }









    public function resolve($account, $bank_code)
    {
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($this->baseUrl."payouts/accounts/banks/resolve",[
                    'account_number' => $account,
                    'bank_code' => $bank_code
                ]);
        return $response;
    }

    public function getBankList()
    {
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($this->baseUrl.'payouts/accounts/banks', [
                    'country' => 'NG',
                ]);
        return $response;
    }

    public function getBank($code)
    {
        $response = $this->getBankList();
        $data = $response['banks'];
        foreach($data as $array):
            if($array["code"] == $code):
                $bank = $array["name"];
            endif;
            global $bank;
        endforeach;
        return $bank;
    }


    public function fetchCurrencies($test_net_only)
    {
        $url = $this->baseUrl.'currencies/';
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($url, [
                    'test_net_only' => $test_net_only  //value = boolean(true or false)
                ]);
        return $response;
    }

    public function validateAddress($address, $network)
    {
        $url = $this->baseUrl.'validateaddress/';
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($url, [
                    'address' => $address,
                    'network' => $network
                ]);
        return $response;
    }

    public function fetchTransactionDetails($reference)
    {
        //$reference is the address unique identifier e.g ADDR_xy
        $url = $this->baseUrl.'address/transactions/'.$reference;
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($url);
        return $response;
    }

    public function fetchTransactions($reference)
    {
        //$reference is the address unique identifier e.g ADDR_xy
        $url = $this->baseUrl.'address/'.$reference.'/transactions';
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($url,[
                    //'page' => 1,
                    //'per_page' => 10
                ]);
        return $response;
    }

    public function fetchExchangeRate($from, $to)
    {
        $url = $this->baseUrl.'rates/';
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->get($url, [
                    'from' => $from,
                    'to' => $to
                ]);
        return $response;
    }

    public function swap(array $data)
    {
        $url = $this->baseUrl.'swaps/';
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->post($url, [
                    'from' => $data[0],
                    'to' => $data[1],
                    'amount' => $data[2], //Amount should be in kobo
                    'currency' => 'NGN'  
                ]);
        return $response;
    }

    public function payout($crypto, $amount, $reference)
    {
        $url = $this->baseUrl.'payouts/';
        $response = Http::acceptJson()
            ->withToken($this->secretKey)
                ->post($url, [
                    'amount' => $amount,
                    //'bank' => $bank,
                    'crypto' => $crypto,  ///crypto address of the recipient
                    //'currency' => '',
                    'recipient' => $reference   //e.g. PAY_ACCT_XYZ
                ]);
        return $response;
    }

}