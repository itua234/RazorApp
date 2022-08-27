<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, 
    WalletController, UserController};

Route::group(['prefix' => 'v1'], function () {
    Route::group([
        'prefix' => 'auth'
    ], function () {
        Route::post("/register", [AuthController::class, "register"]);
        Route::post("/login", [AuthController::class, "login"]);
        Route::post("/google/login", [AuthController::class, "requestTokenGoogle"]);
        Route::post("/sendcode/{email}", [AuthController::class, "sendcode"]);
        Route::post("email/verify/", [AuthController::class, "verifyUser"]);
        Route::post("/password/reset", [AuthController::class, "resetPassword"]);
        Route::post("/verify/reset/{email}/{token}", [AuthController::class, "verifyResetPasswordToken"]);
        Route::post("/reset-password", [AuthController::class, "password_reset"]);
    });

});


//protected route using Laravel Sanctum
Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum']],function(){
    Route::group([
        'prefix' => 'auth'
    ], function () {
        Route::get("/logout", [AuthController::class, "logout"]);
        Route::post("/refresh", [AuthController::class, "refresh"]);
        Route::post("/change-password", [AuthController::class, "change_password"]);
        Route::post("/save-fcm-token", [AuthController::class, "saveFCMToken"]);
    });

    Route::group([
        'prefix' => 'user'
    ], function () {
        Route::post("/update-photo", [UserController::class, "updatePhoto"]);
        Route::post("/update-profile", [UserController::class, "saveProfileDetails"]);
        Route::post("/resolve-account", [UserController::class, "resolveAccount"]);
        Route::delete("/delete-bank-detail", [UserController::class, "deleteBankDetail"]);
        Route::delete("/delete-user-account", [UserController::class, "deleteUserAccount"]);
        Route::post("/verify-bvn/{bvn}", [UserController::class, "verifyBVN"]);
        Route::post("/verify-nin/{nin}", [UserController::class, "verifyNIN"]);
    });

    Route::group([
        'prefix' => 'wallet'
    ], function () {
        Route::post("/initiatedeposit", [WalletController::class, "initiateDeposit"]);
        Route::post("/transfer", [WalletController::class, "transfer"]);
    });

});
