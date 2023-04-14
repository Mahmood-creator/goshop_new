<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService\AuthByEmail;
use App\Services\AuthService\AuthByMobilePhone;
use App\Traits\ApiResponse;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerifyAuthController extends Controller
{
    use ApiResponse;

//    public function verifyEmail(Request $request): \Illuminate\Http\JsonResponse
//    {
//        return (new AuthByEmail())->confirmOPTCode($request->all());
//    }

    public function verifyPhone(Request $request)
    {
        return (new AuthByMobilePhone())->confirmOPTCode($request->all());
    }


    public function verifyEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = User::find($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse('Email already verified', [
                'email' => $user->email,
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->successResponse('Email successfully verified', [
            'email' => $user->email,
        ]);
    }

}
