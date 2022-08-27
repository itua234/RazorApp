<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Requests\{ SavePhoto};

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function updatePhoto(SavePhoto $request)
    {
        return $this->userService->updateProfilePhoto($request);
    }

    public function saveProfileDetails(Request $request)
    {
        return $this->userService->saveProfileDetails($request);
    }

    public function resolveAccount(Request $request)
    {
        return $this->userService->resolveAccount($request);
    }

    public function deleteBankDetail($id)
    {
        return $this->userService->deleteBankDetail($id);
    }

    public function deleteUserAccount()
    {
        return $this->userService->deleteUserAccount();
    }

    public function verifyBVN($bvn)
    {
        return $this->userService->verifyBVN($bvn);
    }

    public function verifyNIN($nin)
    {
        return $this->userService->verifyNIN($nin);
    }


}
