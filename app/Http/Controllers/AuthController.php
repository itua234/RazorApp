<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use App\Http\Requests\{LoginRequest, VerifyAccount, 
    ResetPassword, ChangePassword, CreateUser};

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(CreateUser $request)
    {
        return $this->authService->register($request);
    }

    public function login(LoginRequest $request)
    {
        return $this->authService->login($request);
    }

    public function requestTokenGoogle(Request $request)
    {
        return $this->authService->requestTokenGoogle($request);
    }

    public function logout()
    {
        return $this->authService->logout();
    }

    public function refresh()
    {
        return $this->authService->refresh();
    }

    public function sendcode($email)
    {
        return $this->authService->sendverificationcode($email);
    }

    public function verifyUser(VerifyAccount $request)
    {
        return $this->authService->verifyUser($request);
    }

    public function verifyUserByLink(VerifyAccount $request)
    {
        return $this->authService->verifyUserByLink($request);
    }

    public function resetPassword(ResetPassword $request)
    {
        return $this->authService->resetPassword($request);
    }

    public function verifyResetToken(Request $request)
    {
        return $this->authService->verifyResetToken($request);
    }

    public function verifyResetTokenByLink(Request $request)
    {
        return $this->authService->verifyResetTokenByLink($request);
    }

    public function password_reset(Request $request)
    {
        return $this->authService->password_reset($request);
    }

    public function change_password(ChangePassword $request)
    {
        return $this->authService->change_password($request);
    }

    public function saveFCMToken(Request $request)
    {
        return $this->authService->saveFCMToken($request);
    }

}
