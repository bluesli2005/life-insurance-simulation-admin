<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\Concerns\FormatsPasswordBrokerResponses;
use App\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

/** 再設定トークンを使ったパスワード更新を扱う。 */
class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;
    use FormatsPasswordBrokerResponses;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;


    protected function credentials(Request $request)
    {
        return $request->only('email', 'password', 'password_confirmation', 'token')
            + ['status' => User::STATUS_ACTIVE];
    }

    protected function sendResetResponse(Request $request, $response)
    {
        return $this->passwordBrokerSuccessResponse($response);
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        return $this->passwordBrokerFailedResponse($response);
    }
}
