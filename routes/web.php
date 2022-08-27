<?php

use Illuminate\Support\Facades\{Route, Auth, DB};
use Illuminate\Http\Request;
use App\Models\{User, Transaction};
use App\Util\CustomResponse;
use Carbon\Carbon;
use PragmaRX\Google2FAQRCode\Google2FA;

Route::group([
], function () {
    /*Route::get('/register', function () {return view('auth.register');})->name('register');
    Route::get('/login', function () {return view('auth.login');})->name('login');
    Route::get('/email/verify', function () {return view('auth.verify-email');})->name('verification.notice');
    Route::get('/forgot-password', function () {return view('auth.forgot-password');})->name('forgot-password');
    Route::get('/reset-password', function (Request $request) {
        return view('auth.reset-password', ['request' => $request]);
    })->name('reset-password');*/

    Route::get('/email', function () {
        return view('email.ver');
    });
    
    Route::get("email/verify/{email}/{code}", [AuthController::class, "verifyUserByLink"]);
    Route::get("/verify/reset/{email}/{token}", [AuthController::class, "verifyResetTokenByLink"]);

    Route::get('/test-two-factor', function () {
        $google2fa = new Google2FA();
        $secretKey = $google2fa->generateSecretKey();
        $inlineUrl = $google2fa->getQRCodeInline(
            'RazorWallet',
            'ituaosemeilu234@gmail.com',
            $secretKey
        );
        
        return CustomResponse::success('success', [
            'key' => $secretKey,
            'url' => $inlineUrl
        ]);
    });
});


