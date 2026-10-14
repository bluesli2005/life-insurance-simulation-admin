<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\Concerns\FormatsPasswordBrokerResponses;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

/** パスワード再設定メールの送信を扱う。 */
class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;
    use FormatsPasswordBrokerResponses;


    protected function credentials(Request $request)
    {
        return $request->only('email') + ['status' => User::STATUS_ACTIVE];
    }

    protected function sendResetLinkResponse(Request $request, $response)
    {
        return $this->passwordBrokerSuccessResponse($response);
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return $this->passwordBrokerFailedResponse($response);
    }
}
