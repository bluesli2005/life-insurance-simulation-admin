<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;

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

    public function showLinkRequestForm()
    {
        return response()->file(resource_path('spa.html'), ['Cache-Control' => 'no-store'])->setPrivate();
    }

    protected function sendResetLinkResponse(Request $request, $response)
    {
        return response()->json(['message' => trans($response)]);
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return response()->json([
            'errors' => ['email' => [trans($response)]],
        ], $response === PasswordBrokerContract::RESET_THROTTLED ? 429 : 422);
    }
}
